// Constants for selectors and classes
const Selectors = {
    ORIXA: '.orixa',
    ORIXA_HEADER: '.orixa-header',
    SECTION: 'section',
    SECTION_HEADER: 'section h2',
    BLOCK: '.block',
    BLOCK_HEADER: '.block-header',
    TOGGLE_BTN: '.toggle-btn'
};

const Classes = {
    EXPANDED: 'expanded',
    COLLAPSED: 'collapsed'
};

// Utility functions
const getElements = (selector) => document.querySelectorAll(selector);
const getElement = (parent, selector) => parent.querySelector(selector);

// Initialize on DOM load
document.addEventListener('DOMContentLoaded', () =>
{
    initializeInteractions();
});

// Initialize all interactions
function initializeInteractions()
{
    setupOrixaToggles();
    setupSectionToggles();
    setupBlockToggles();

    logInitialization();
}

// Setup Orixá toggle functionality
function setupOrixaToggles()
{
    const orixas = getElements(Selectors.ORIXA);

    orixas.forEach(orixa =>
    {
        const header = getElement(orixa, Selectors.ORIXA_HEADER);

        if (header)
        {
            header.addEventListener('click', () =>
            {
                orixa.classList.toggle(Classes.COLLAPSED);
            });
        }
    });
}

// Setup Section toggle functionality
function setupSectionToggles()
{
    const sections = getElements(Selectors.SECTION);

    sections.forEach(section =>
    {
        const header = getElement(section, 'h2');

        if (header)
        {
            header.addEventListener('click', () =>
            {
                section.classList.toggle(Classes.COLLAPSED);
            });
        }
    });
}

// Setup Block toggle functionality
function setupBlockToggles()
{
    const blocks = getElements(Selectors.BLOCK);

    blocks.forEach(block =>
    {
        const header = getElement(block, Selectors.BLOCK_HEADER);
        const toggleBtn = getElement(block, Selectors.TOGGLE_BTN);

        const toggleExpand = () =>
        {
            block.classList.toggle(Classes.EXPANDED);
        };

        if (header)
        {
            header.addEventListener('click', toggleExpand);
        }

        if (toggleBtn)
        {
            toggleBtn.addEventListener('click', (e) =>
            {
                e.stopPropagation();
                toggleExpand();
            });
        }
    });
}

// Log initialization info
function logInitialization()
{
    const orixaCount = getElements(Selectors.ORIXA).length;
    const sectionCount = getElements(Selectors.SECTION).length;
    const blockCount = getElements(Selectors.BLOCK).length;

    console.log(`Rezas de Cabinda inicializadas: ${orixaCount} orixás, ${sectionCount} seções, ${blockCount} cantos`);
}

// Global control functions
window.expandAll = () =>
{
    getElements(Selectors.BLOCK).forEach(block =>
    {
        block.classList.add(Classes.EXPANDED);
    });
};

window.collapseAll = () =>
{
    getElements(Selectors.BLOCK).forEach(block =>
    {
        block.classList.remove(Classes.EXPANDED);
    });
};

window.expandAllSections = () =>
{
    getElements(Selectors.SECTION).forEach(section =>
    {
        section.classList.remove(Classes.COLLAPSED);
    });
};

window.collapseAllSections = () =>
{
    getElements(Selectors.SECTION).forEach(section =>
    {
        section.classList.add(Classes.COLLAPSED);
    });
};

window.expandAllOrixas = () =>
{
    getElements(Selectors.ORIXA).forEach(orixa =>
    {
        orixa.classList.remove(Classes.COLLAPSED);
    });
};

window.collapseAllOrixas = () =>
{
    getElements(Selectors.ORIXA).forEach(orixa =>
    {
        orixa.classList.add(Classes.COLLAPSED);
    });
};