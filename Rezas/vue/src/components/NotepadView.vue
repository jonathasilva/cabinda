<script setup>
import { computed, ref } from "vue";

const props = defineProps(
{
    rezas: Array,
    orixaName: String,
    color: String,
});

const copiedAll = ref(false);
const selectedReza = ref(null);

const allText = computed(() =>
{
    const source = selectedReza.value
        ? props.rezas.filter(r => r.id === selectedReza.value)
        : props.rezas;

    return source
        .map(reza =>
        {
            const lines = reza.cantos
                .filter(c => c.transliteracao)
                .map(c =>
                {
                    const prefix = c.sing === "P" ? "(P) "
                        : c.sing === "R" ? "(R) "
                        : "";
                    return `${prefix}${c.transliteracao}`;
                });
            return `── ${reza.titulo} ──\n\n${lines.join("\n")}`;
        })
        .join("\n\n\n");
});

async function copyAll()
{
    try
    {
        await navigator.clipboard.writeText(allText.value);
    }
    catch
    {
        const ta = document.createElement("textarea");
        ta.value = allText.value;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand("copy");
        document.body.removeChild(ta);
    }
    copiedAll.value = true;
    setTimeout(() =>
    {
        copiedAll.value = false;
    }, 2000);
}

function selectAll()
{
    const el = document.getElementById("notepad-content");
    if (!el)
    {
        return;
    }
    const range = document.createRange();
    range.selectNodeContents(el);
    const sel = window.getSelection();
    sel?.removeAllRanges();
    sel?.addRange(range);
}
</script>

<template>
    <div class="notepad">
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="toolbar-left">
                <select
                    v-model="selectedReza"
                    class="reza-select"
                >
                    <option :value="null">
                        Todas as rezas
                    </option>
                    <option
                        v-for="reza in rezas"
                        :key="reza.id"
                        :value="reza.id"
                    >
                        {{ reza.titulo }}
                    </option>
                </select>
            </div>

            <div class="toolbar-right">
                <button
                    class="tool-btn"
                    @click="selectAll"
                    title="Selecionar tudo"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                    >
                        <rect
                            x="2"
                            y="2"
                            width="12"
                            height="12"
                            rx="2"
                            stroke="currentColor"
                            stroke-width="1.2"
                            stroke-dasharray="2 2"
                        />
                    </svg>
                    Selecionar
                </button>

                <button
                    class="tool-btn"
                    :class="{ copied: copiedAll }"
                    @click="copyAll"
                    title="Copiar tudo"
                >
                    <svg
                        v-if="!copiedAll"
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
                    {{ copiedAll ? "Copiado!" : "Copiar" }}
                </button>
            </div>
        </div>

        <!-- Text content -->
        <pre
            id="notepad-content"
            class="notepad-text"
        >{{ allText }}</pre>
    </div>
</template>

<style scoped>
.notepad
{
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
}

.toolbar
{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 16px;
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
}

.toolbar-left
{
    flex: 1;
    min-width: 140px;
}

.toolbar-right
{
    display: flex;
    gap: 6px;
}

.reza-select
{
    width: 100%;
    max-width: 260px;
    padding: 6px 10px;
    background: var(--bg);
    color: var(--text);
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.8rem;
    font-family: inherit;
    cursor: pointer;
    outline: none;
}

.reza-select:focus
{
    border-color: var(--text-muted);
}

.tool-btn
{
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--text-secondary);
    border: 1px solid var(--border);
    transition: all 0.15s ease;
    white-space: nowrap;
}

.tool-btn:hover
{
    background: var(--surface-hover);
    color: var(--text);
}

.tool-btn.copied
{
    color: #4ade80;
    border-color: #4ade8040;
}

.notepad-text
{
    padding: 20px;
    margin: 0;
    background: var(--bg);
    font-family: "Inter", system-ui, sans-serif;
    font-size: 0.95rem;
    line-height: 1.8;
    color: var(--text);
    white-space: pre-wrap;
    word-break: break-word;
    min-height: 300px;
    max-height: 70vh;
    overflow-y: auto;
    cursor: text;
    user-select: text;
    -webkit-user-select: text;
}

/* Selection highlight */
.notepad-text::selection
{
    background: #ffffff25;
}
</style>