<script setup>
defineProps(
{
    orixas: Array,
    colors: Object,
    names: Object,
    selected: String,
});

defineEmits(["select"]);
</script>

<template>
    <nav class="nav">
        <button
            v-for="key in orixas"
            :key="key"
            class="nav-btn"
            :class="{ active: selected === key }"
            :style="{
                '--accent': colors[key]?.hex || '#555',
            }"
            @click="$emit('select', key)"
        >
            <span
                class="dot"
                :style="{ background: colors[key]?.hex || '#555' }"
            ></span>
            <span class="label">{{ names[key] || key }}</span>
        </button>
    </nav>
</template>

<style scoped>
.nav
{
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: center;
    margin-bottom: 32px;
}

.nav-btn
{
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 100px;
    background: var(--surface);
    border: 1px solid var(--border);
    font-size: 0.82rem;
    font-weight: 500;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.nav-btn:hover
{
    background: var(--surface-hover);
}

.nav-btn.active
{
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
}

.dot
{
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}

.nav-btn.active .dot
{
    background: rgba(255, 255, 255, 0.6) !important;
}
</style>