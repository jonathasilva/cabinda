import os
import numpy as np
import librosa
import soundfile as sf
from pathlib import Path
import re

# Full path to the folder containing MP3 files
folder_path = r"F:\Jonatha\Músicas\NovoPC\2025-PosFor\REZANDO AOS ORIXÁS"

def detect_silence_ranges(audio_data, sample_rate, silence_threshold_db=-40, min_silence_duration=0.35):
    """
    Detects silence ranges in audio data. 
    
    Args:
        audio_data:  Audio samples as numpy array
        sample_rate: Sample rate of the audio
        silence_threshold_db:  Silence threshold in dB (default: -40)
        min_silence_duration: Minimum silence duration in seconds (default: 0.2)
    
    Returns:
        List of tuples (start_sample, end_sample) for non-silent regions
    """
    
    # Convert to dB
    audio_db = librosa.amplitude_to_db(np.abs(audio_data), ref=np.max)
    
    # Find samples below threshold
    is_silent = audio_db < silence_threshold_db
    
    # Find transitions
    silent_to_sound = np.diff(is_silent. astype(int)) == -1
    sound_to_silent = np.diff(is_silent. astype(int)) == 1
    
    # Get indices of transitions
    start_indices = np.where(silent_to_sound)[0] + 1
    end_indices = np.where(sound_to_silent)[0] + 1
    
    # Handle edge cases
    if len(start_indices) == 0 and len(end_indices) == 0:
        # No silence detected, return entire audio as one segment
        if not np.all(is_silent):
            return [(0, len(audio_data))]
        else:
            return []
    
    if not is_silent[0]:
        start_indices = np.insert(start_indices, 0, 0)
    
    if not is_silent[-1]:
        end_indices = np.append(end_indices, len(audio_data))
    
    # Ensure equal number of starts and ends
    min_length = min(len(start_indices), len(end_indices))
    start_indices = start_indices[:min_length]
    end_indices = end_indices[:min_length]
    
    # Filter by minimum silence duration
    min_silence_samples = int(min_silence_duration * sample_rate)
    
    segments = []
    for start, end in zip(start_indices, end_indices):
        # Check if previous silence was long enough
        if not segments or (start - segments[-1][1]) >= min_silence_samples:
            segments. append((start, end))
        else:
            # Merge with previous segment if silence was too short
            segments[-1] = (segments[-1][0], end)
    
    return segments


def trim_silence(audio_data, sample_rate, silence_threshold_db=-40):
    """
    Trims silence from the beginning and end of audio data.
    
    Args:
        audio_data: Audio samples as numpy array
        sample_rate: Sample rate of the audio
        silence_threshold_db: Silence threshold in dB
    
    Returns:
        Trimmed audio data
    """
    
    # Convert to dB
    audio_db = librosa.amplitude_to_db(np.abs(audio_data), ref=np.max)
    
    # Find non-silent samples
    non_silent = audio_db >= silence_threshold_db
    non_silent_indices = np. where(non_silent)[0]
    
    if len(non_silent_indices) == 0:
        return audio_data  # Return original if all silent
    
    # Trim
    start = non_silent_indices[0]
    end = non_silent_indices[-1] + 1
    
    return audio_data[start:end]


def simplify_filename(filename):
    """
    Simplifies filename by removing prefixes and special characters.
    
    Args:
        filename: Original filename (without extension)
    
    Returns:
        Simplified filename
    """
    # Remove number prefix (e.g., "01_")
    result = re.sub(r'^\d+_', '', filename)
    
    # Remove "Rezas de" pattern
    result = result.replace('Rezas de ', '').replace('Rezas ', '')
    
    # Clean up whitespace
    result = result.strip()
    
    # Remove special characters and replace spaces with underscores
    result = re.sub(r'[^\w\s-]', '', result)
    result = result. replace(' ', '_')
    
    return result


def split_and_trim_audio(file_path, silence_threshold=-40, min_silence_duration=0.35):
    """
    Splits an MP3 audio file based on silence periods and trims silence from each part.
    
    Args:
        file_path: Full path to the MP3 file
        silence_threshold: Silence threshold in dB (default: -40)
        min_silence_duration:  Minimum silence duration in seconds (default: 0.2)
    """
    
    # Check if file exists
    if not os. path.exists(file_path):
        print(f"Error:  File not found: {file_path}")
        return
    
    # Get the directory and filename
    file_path_obj = Path(file_path)
    file_dir = file_path_obj. parent
    file_name = file_path_obj. stem
    
    # Simplify filename for output folder
    simplified_name = simplify_filename(file_name)
    
    # Create output directory with simplified name
    output_dir = file_dir / simplified_name
    output_dir. mkdir(exist_ok=True)
    
    print(f"\n{'='*80}")
    print(f"Processing:  {file_name}")
    print(f"Output folder: {output_dir}")
    print(f"{'='*80}")
    
    print(f"Loading audio file: {file_path}")
    
    # Load the audio file
    audio_data, sample_rate = librosa.load(file_path, sr=None, mono=True)
    
    duration = len(audio_data) / sample_rate
    total_samples = len(audio_data)
    print(f"Audio duration: {duration:.2f} seconds")
    print(f"Total samples: {total_samples: ,}")
    print(f"Sample rate: {sample_rate} Hz")
    print(f"Splitting audio on silence (>= {min_silence_duration * 1000:.0f}ms)...")
    
    # Detect non-silent segments
    segments = detect_silence_ranges(
        audio_data,
        sample_rate,
        silence_threshold_db=silence_threshold,
        min_silence_duration=min_silence_duration
    )
    
    if not segments:
        print("No audio segments detected.")
        return
    
    print(f"\n📊 ANALYSIS:")
    print(f"   Found {len(segments)} audio segments")
    print(f"   Silence threshold: {silence_threshold} dB")
    print(f"   Minimum silence duration: {min_silence_duration * 1000:.0f} ms")
    
    print("\n🎵 SEGMENTS:")
    
    # Export each segment
    saved_count = 0
    for i, (start, end) in enumerate(segments, start=1):
        segment_audio = audio_data[start:end]
        
        # Trim silence from this segment
        trimmed_audio = trim_silence(segment_audio, sample_rate, silence_threshold)
        
        original_duration = len(segment_audio) / sample_rate
        trimmed_duration = len(trimmed_audio) / sample_rate
        trimmed_amount = original_duration - trimmed_duration
        
        # Format output filename:  01_Bara_part_1.mp3
        output_filename = f"{i: 02d}_{simplified_name}_part_{i}.mp3"
        output_path = output_dir / output_filename
        
        start_time = start / sample_rate
        end_time = end / sample_rate
        
        print(f"\n   [{i: 02d}/{len(segments)}] {format_time(start_time)} → {format_time(end_time)} (duration: {original_duration:.2f}s)")
        print(f"        Extracting to: {output_filename}")
        print(f"        Original: {original_duration:.2f}s | Trimmed: {trimmed_duration:.2f}s | Removed: {trimmed_amount:.2f}s")
        
        try:
            # Export as MP3
            sf.write(str(output_path), trimmed_audio, sample_rate, format='mp3')
            file_size = output_path.stat().st_size / 1024  # KB
            saved_count += 1
            print(f"        ✓ Success!  Size: {file_size:.2f} KB")
        except Exception as e:
            print(f"        ✗ Error saving file: {e}")
    
    # Verify last segment was included
    last_segment_end = segments[-1][1]
    print(f"\n📈 COVERAGE:")
    print(f"   Last segment ends at sample: {last_segment_end:,} of {total_samples:,}")
    print(f"   Coverage: {(last_segment_end / total_samples) * 100:.2f}% of audio")
    print(f"\n✅ Completed! {saved_count}/{len(segments)} files saved to: {output_dir}")


def format_time(seconds):
    """
    Formats seconds to MM:SS. mmm format.
    
    Args:
        seconds: Time in seconds
    
    Returns: 
        Formatted time string
    """
    minutes = int(seconds // 60)
    secs = int(seconds % 60)
    millis = int((seconds % 1) * 1000)
    return f"{minutes:02d}:{secs:02d}.{millis:03d}"


def process_folder(folder_path, silence_threshold=-40, min_silence_duration=0.2):
    """
    Processes all MP3 files in the specified folder.
    
    Args:
        folder_path: Path to folder containing MP3 files
        silence_threshold: Silence threshold in dB (default: -40)
        min_silence_duration:  Minimum silence duration in seconds (default:  0.2)
    """
    
    folder = Path(folder_path)
    
    if not folder.exists():
        print(f"Error:  Folder not found: {folder_path}")
        return
    
    # Find all MP3 files
    mp3_files = sorted(folder.glob("*.mp3"))
    
    if not mp3_files:
        print(f"No MP3 files found in: {folder_path}")
        return
    
    print(f"Found {len(mp3_files)} MP3 file(s) in folder")
    print(f"Folder: {folder_path}")
    
    # Process each file
    for i, mp3_file in enumerate(mp3_files, start=1):
        print(f"\n\n{'#'*80}")
        print(f"FILE {i}/{len(mp3_files)}")
        print(f"{'#'*80}")
        
        split_and_trim_audio(
            str(mp3_file),
            silence_threshold=silence_threshold,
            min_silence_duration=min_silence_duration
        )
    
    print(f"\n\n{'='*80}")
    print(f"ALL FILES PROCESSED!")
    print(f"Total files:  {len(mp3_files)}")
    print(f"{'='*80}")


if __name__ == "__main__":
    process_folder(folder_path, silence_threshold=-40, min_silence_duration=0.35)