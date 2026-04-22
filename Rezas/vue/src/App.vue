<script setup>
import { onMounted, ref } from "vue";
import NotepadView from "./components/NotepadView.vue";
import OrixaNav from "./components/OrixaNav.vue";
import RezaList from "./components/RezaList.vue";

const data = ref(null);
const selectedOrixa = ref(null);
const viewMode = ref("cards"); // "cards" or "notepad"

const orixaNames =
{
    bara: "Bará",
    ogun: "Ògún",
    oya: "Ọya",
    xango: "Ṣàngó",
    "ode-ati-otin": "Ọdẹ & Òtìn",
    ossain: "Ossayin",
    xapana: "Ṣọ̀npọ̀nná",
    oba: "Ọbà",
    ibeji: "Ìbéjì",
    oxum: "Ọ̀ṣùn",
    yemoja: "Yemọjá",
    oxala: "Òṣàlá",
};

onMounted(async () =>
{
    const res = await fetch(`${import.meta.env.BASE_URL}rezas.json`);
    data.value = await res.json();
});

function selectOrixa(key)
{
    selectedOrixa.value = selectedOrixa.value === key ? null : key;
    if (selectedOrixa.value)
    {
        setTimeout(() =>
        {
            document
                .getElementById("reza-content")
                ?.scrollIntoView({ behavior: "smooth", block: "start" });
        }, 100);
    }
}
</script>

<template>
    <div v-if="data">
        <header class="header">
            <h1 class="title">Rezas</h1>
            <p class="subtitle">Cantigas sagradas do Batuque</p>
        </header>

        <OrixaNav
            :orixas="Object.keys(data.rezas)"
            :colors="data.settings.colors"
            :names="orixaNames"
            :selected="selectedOrixa"
            @select="selectOrixa"
        />

        <!-- View mode toggle -->
        <div
            v-if="selectedOrixa && data.rezas[selectedOrixa]?.length"
            class="mode-bar"
        >
            <div class="mode-toggle">
                <button
                    class="mode-btn"
                    :class="{ active: viewMode === 'cards' }"
                    @click="viewMode = 'cards'"
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
                            width="5"
                            height="5"
                            rx="1"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                        <rect
                            x="9"
                            y="2"
                            width="5"
                            height="5"
                            rx="1"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                        <rect
                            x="2"
                            y="9"
                            width="5"
                            height="5"
                            rx="1"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                        <rect
                            x="9"
                            y="9"
                            width="5"
                            height="5"
                            rx="1"
                            stroke="currentColor"
                            stroke-width="1.2"
                        />
                    </svg>
                    Cards
                </button>
                <button
                    class="mode-btn"
                    :class="{ active: viewMode === 'notepad' }"
                    @click="viewMode = 'notepad'"
                >
                    <svg
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                    >
                        <path
                            d="M4 4h8M4 7h8M4 10h5"
                            stroke="currentColor"
                            stroke-width="1.2"
                            stroke-linecap="round"
                        />
                    </svg>
                    Notepad
                </button>
            </div>
        </div>

        <!-- Content area -->
        <div
            v-if="selectedOrixa && data.rezas[selectedOrixa]?.length"
            id="reza-content"
        >
            <RezaList
                v-if="viewMode === 'cards'"
                :rezas="data.rezas[selectedOrixa]"
                :color="data.settings.colors[selectedOrixa]?.hex"
                :orixaName="orixaNames[selectedOrixa] || selectedOrixa"
            />

            <NotepadView
                v-else
                :rezas="data.rezas[selectedOrixa]"
                :color="data.settings.colors[selectedOrixa]?.hex"
                :orixaName="orixaNames[selectedOrixa] || selectedOrixa"
            />
        </div>

        <div
            v-else-if="selectedOrixa && !data.rezas[selectedOrixa]?.length"
            class="empty"
        >
            <p>Nenhuma reza cadastrada para {{ orixaNames[selectedOrixa] }}.</p>
        </div>
    </div>

    <div v-else class="loading">
        <div class="spinner"></div>
    </div>
</template>

<style scoped>
.header
{
    text-align: center;
    margin-bottom: 32px;
    padding-top: 16px;
}

.title
{
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.subtitle
{
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-top: 4px;
    font-weight: 300;
}

.mode-bar
{
    display: flex;
    justify-content: flex-end;
    margin-bottom: 16px;
}

.mode-toggle
{
    display: flex;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.mode-btn
{
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 7px 14px;
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted);
    transition: all 0.15s ease;
    border-right: 1px solid var(--border);
}

.mode-btn:last-child
{
    border-right: none;
}

.mode-btn:hover
{
    color: var(--text-secondary);
    background: var(--surface-hover);
}

.mode-btn.active
{
    color: var(--text);
    background: var(--surface-hover);
}

.empty
{
    text-align: center;
    padding: 48px 16px;
    color: var(--text-muted);
}

.loading
{
    display: flex;
    justify-content: center;
    padding-top: 40vh;
}

.spinner
{
    width: 32px;
    height: 32px;
    border: 2px solid var(--border);
    border-top-color: var(--text-secondary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin
{
    to { transform: rotate(360deg); }
}
</style>