<script setup>
import { ref } from "vue";
import CantoCard from "./CantoCard.vue";

const props = defineProps(
{
    rezas: Array,
    color: String,
    orixaName: String,
});

const expandedReza = ref(null);

function toggle(id)
{
    expandedReza.value = expandedReza.value === id ? null : id;
}
</script>

<template>
    <div class="reza-list">
        <div
            v-for="reza in rezas"
            :key="reza.id"
            class="reza-section"
        >
            <button
                class="reza-header"
                :class="{ open: expandedReza === reza.id }"
                @click="toggle(reza.id)"
            >
                <div class="reza-header-left">
                    <span
                        class="accent-bar"
                        :style="{ background: color }"
                    ></span>
                    <div>
                        <h2 class="reza-title">{{ reza.titulo }}</h2>
                        <span class="reza-count">
                            {{ reza.cantos.length }} canto{{ reza.cantos.length !== 1 ? "s" : "" }}
                        </span>
                    </div>
                </div>
                <svg
                    class="chevron"
                    :class="{ rotated: expandedReza === reza.id }"
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    fill="none"
                >
                    <path
                        d="M6 8l4 4 4-4"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </button>

            <Transition name="slide">
                <div
                    v-if="expandedReza === reza.id"
                    class="cantos-container"
                >
                    <CantoCard
                        v-for="(canto, idx) in reza.cantos"
                        :key="idx"
                        :canto="canto"
                        :index="idx"
                        :color="color"
                    />
                </div>
            </Transition>
        </div>
    </div>
</template>

<style scoped>
.reza-list
{
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.reza-section
{
    border-radius: var(--radius);
    overflow: hidden;
}

.reza-header
{
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    text-align: left;
    transition: all 0.2s ease;
}

.reza-header.open
{
    border-radius: var(--radius) var(--radius) 0 0;
    border-bottom-color: transparent;
}

.reza-header:hover
{
    background: var(--surface-hover);
}

.reza-header-left
{
    display: flex;
    align-items: center;
    gap: 14px;
}

.accent-bar
{
    width: 4px;
    height: 32px;
    border-radius: 2px;
    flex-shrink: 0;
}

.reza-title
{
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.3;
}

.reza-count
{
    font-size: 0.75rem;
    color: var(--text-muted);
}

.chevron
{
    color: var(--text-muted);
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.chevron.rotated
{
    transform: rotate(180deg);
}

.cantos-container
{
    display: flex;
    flex-direction: column;
    gap: 2px;
    background: var(--border);
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 var(--radius) var(--radius);
    overflow: hidden;
}

/* Transition */
.slide-enter-active,
.slide-leave-active
{
    transition: all 0.25s ease;
    overflow: hidden;
}

.slide-enter-from,
.slide-leave-to
{
    max-height: 0;
    opacity: 0;
}

.slide-enter-to,
.slide-leave-from
{
    max-height: 5000px;
    opacity: 1;
}
</style>