<script setup>
import { ref } from "vue";

const props = defineProps(
{
    canto: Object,
    index: Number,
    color: String,
});

const showTranslation = ref(false);
const showNotes = ref(false);
const copiedField = ref(null);

const singLabels =
{
    P: "Puxada",
    R: "Resposta",
    none: "",
};

async function copyText(text, field)
{
    try
    {
        await navigator.clipboard.writeText(text);
        copiedField.value = field;
        setTimeout(() =>
        {
            copiedField.value = null;
        }, 1500);
    }
    catch (err)
    {
        // Fallback
        const ta = document.createElement("textarea");
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand("copy");
        document.body.removeChild(ta);
        copiedField.value = field;
        setTimeout(() =>
        {
            copiedField.value = null;
        }, 1500);
    }
}

function copyAll()
{
    const parts = [];
    if (props.canto.yoruba)
    {
        parts.push(`Yorubá: ${props.canto.yoruba}`);
    }
    if (props.canto.transliteracao)
    {
        parts.push(`Pronúncia: ${props.canto.transliteracao}`);
    }
    if (props.canto.traducao)
    {
        parts.push(`Tradução: ${props.canto.traducao}`);
    }
    copyText(parts.join("\n"), "all");
}
</script>

<template>
    <div class="canto">
        <!-- Sing badge -->
        <div class="canto-top">
            <span
                v-if="canto.sing !== 'none'"
                class="sing-badge"
                :style="{
                    background: canto.sing === 'P' ? color + '30' : '#ffffff15',
                    color: canto.sing === 'P' ? color : 'var(--text-secondary)',
                }"
            >
                {{ singLabels[canto.sing] || canto.sing }}
            </span>
            <span class="canto-num">#{{ index + 1 }}</span>
        </div>

        <!-- Pronunciation (main) -->
        <div
            v-if="canto.transliteracao"
            class="field main-field"
        >
            <p class="pronunciation">{{ canto.transliteracao }}</p>
            <button
                class="copy-btn"
                :class="{ copied: copiedField === 'trans' }"
                @click="copyText(canto.transliteracao, 'trans')"
                :title="copiedField === 'trans' ? 'Copiado!' : 'Copiar pronúncia'"
            >
                <svg
                    v-if="copiedField !== 'trans'"
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                >
                    <rect
                        x="5"
                        y="5"
                        width="8"
                        height="8"
                        rx="1.5"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                    <path
                        d="M3 11V3.5A1.5 1.5 0 014.5 2H11"
                        stroke="currentColor"
                        stroke-width="1.2"
                        stroke-linecap="round"
                    />
                </svg>
                <svg
                    v-else
                    width="16"
                    height="16"
                    viewBox="0 0 16 16"
                    fill="none"
                >
                    <path
                        d="M4 8.5l3 3 5-6"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>
        </div>

        <!-- Yorubá (secondary) -->
        <div
            v-if="canto.yoruba"
            class="field secondary-field"
        >
            <p class="yoruba">{{ canto.yoruba }}</p>
            <button
                class="copy-btn small"
                :class="{ copied: copiedField === 'yoruba' }"
                @click="copyText(canto.yoruba, 'yoruba')"
                :title="copiedField === 'yoruba' ? 'Copiado!' : 'Copiar Yorubá'"
            >
                <svg
                    v-if="copiedField !== 'yoruba'"
                    width="14"
                    height="14"
                    viewBox="0 0 16 16"
                    fill="none"
                >
                    <rect
                        x="5"
                        y="5"
                        width="8"
                        height="8"
                        rx="1.5"
                        stroke="currentColor"
                        stroke-width="1.2"
                    />
                    <path
                        d="M3 11V3.5A1.5 1.5 0 014.5 2H11"
                        stroke="currentColor"
                        stroke-width="1.2"
                        stroke-linecap="round"
                    />
                </svg>
                <svg
                    v-else
                    width="14"
                    height="14"
                    viewBox="0 0 16 16"
                    fill="none"
                >
                    <path
                        d="M4 8.5l3 3 5-6"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>
        </div>

        <!-- Actions row -->
        <div class="actions">
            <button
                v-if="canto.traducao"
                class="action-btn"
                @click="showTranslation = !showTranslation"
            >
                {{ showTranslation ? "Ocultar tradução" : "Ver tradução" }}
            </button>

            <button
                v-if="canto.notetexts?.length"
                class="action-btn"
                @click="showNotes = !showNotes"
            >
                {{ showNotes ? "Ocultar notas" : "Notas" }}
                <span class="note-count">{{ canto.notetexts.length }}</span>
            </button>

            <button
                class="action-btn copy-all-btn"
                :class="{ copied: copiedField === 'all' }"
                @click="copyAll"
            >
                {{ copiedField === "all" ? "✓ Copiado" : "Copiar tudo" }}
            </button>
        </div>

        <!-- Translation (hidden by default) -->
        <Transition name="fade">
            <div
                v-if="showTranslation && canto.traducao"
                class="translation"
            >
                <p>{{ canto.traducao }}</p>
                <button
                    class="copy-btn small"
                    :class="{ copied: copiedField === 'trad' }"
                    @click="copyText(canto.traducao, 'trad')"
                >
                    <svg
                        v-if="copiedField !== 'trad'"
                        width="14"
                        height="14"
                        viewBox="0 0 16 16"
                        fill="none"
                    >
                        <rect
                            x="5"
                            y="5"
                            width="8"
                            height="8"
                            rx="1.5"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                        <path
                            d="M3 11V3.5A1.5 1.5 0 014.5 2H11"
                            stroke="currentColor"
                            stroke-width="1.2"
                            stroke-linecap="round"
                        />
                    </svg>
                    <svg
                        v-else
                        width="14"
                        height="14"
                        viewBox="0 0 16 16"
                        fill="none"
                    >
                        <path
                            d="M4 8.5l3 3 5-6"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </button>
            </div>
        </Transition>

        <!-- Notes (hidden by default) -->
        <Transition name="fade">
            <div
                v-if="showNotes && canto.notetexts?.length"
                class="notes"
            >
                <p
                    v-for="(note, ni) in canto.notetexts"
                    :key="ni"
                    class="note-text"
                >
                    {{ note }}
                </p>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.canto
{
    background: var(--surface);
    padding: 16px 20px;
}

.canto-top
{
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.sing-badge
{
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 100px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.canto-num
{
    font-size: 0.7rem;
    color: var(--text-muted);
    font-weight: 500;
}

.field
{
    display: flex;
    align-items: flex-start;
    gap: 10px;
    justify-content: space-between;
}

.main-field
{
    margin-bottom: 8px;
}

.pronunciation
{
    font-size: 1.05rem;
    font-weight: 500;
    line-height: 1.5;
    color: var(--text);
}

.secondary-field
{
    margin-bottom: 10px;
}

.yoruba
{
    font-size: 0.82rem;
    color: var(--text-secondary);
    line-height: 1.5;
    font-style: italic;
}

.copy-btn
{
    flex-shrink: 0;
    padding: 6px;
    border-radius: 6px;
    color: var(--text-muted);
    transition: all 0.15s ease;
}

.copy-btn:hover
{
    background: var(--surface-hover);
    color: var(--text-secondary);
}

.copy-btn.copied
{
    color: #4ade80;
}

.copy-btn.small
{
    padding: 4px;
}

.actions
{
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.action-btn
{
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 6px;
    color: var(--text-secondary);
    border: 1px solid var(--border);
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 4px;
}

.action-btn:hover
{
    background: var(--surface-hover);
    color: var(--text);
}

.copy-all-btn.copied
{
    color: #4ade80;
    border-color: #4ade8040;
}

.note-count
{
    background: var(--border);
    padding: 0 5px;
    border-radius: 4px;
    font-size: 0.65rem;
}

.translation
{
    margin-top: 10px;
    padding: 12px;
    background: #ffffff06;
    border-radius: var(--radius-sm);
    border-left: 3px solid var(--text-muted);
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.translation p
{
    font-size: 0.82rem;
    color: var(--text-secondary);
    line-height: 1.5;
}

.notes
{
    margin-top: 8px;
    padding: 12px;
    background: #ffffff04;
    border-radius: var(--radius-sm);
}

.note-text
{
    font-size: 0.78rem;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 6px;
}

.note-text:last-child
{
    margin-bottom: 0;
}

/* Transitions */
.fade-enter-active,
.fade-leave-active
{
    transition: all 0.2s ease;
}

.fade-enter-from,
.fade-leave-to
{
    opacity: 0;
    transform: translateY(-4px);
}
</style>