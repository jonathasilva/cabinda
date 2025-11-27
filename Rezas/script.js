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
            header.addEventListener('click', (e) =>
            {
                if (e.ctrlKey || e.metaKey)
                {
                    const sections = getElements(Selectors.SECTION);
                    const allCollapsed = Array.from(sections).every(section => section.classList.contains(Classes.COLLAPSED));
                    sections.forEach(section =>
                    {
                        if (allCollapsed)
                        {
                            section.classList.remove(Classes.COLLAPSED);
                        }
                        else
                        {
                            section.classList.add(Classes.COLLAPSED);
                        }
                    });

                    if (orixa.classList.contains(Classes.COLLAPSED))
                    {
                        orixa.classList.remove(Classes.COLLAPSED);
                    }
                }
                else
                {
                    orixa.classList.toggle(Classes.COLLAPSED);

                    if (orixa.classList.contains(Classes.COLLAPSED))
                    {
                        // Collapse all sections inside the orixa when collapsing the orixa
                        const sections = getElements(Selectors.SECTION);
                        sections.forEach(section =>
                        {
                            section.classList.add(Classes.COLLAPSED);
                        });
                    }
                }

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
            header.addEventListener('click', (e) =>
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

        const toggleExpand = (e) =>
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

window.expandAllSectionsInOrixa = (orixaId) =>
{
    const orixa = document.getElementById(orixaId);
    if (orixa)
    {
        const sections = getElements.call(orixa, Selectors.SECTION);
        sections.forEach(section =>
        {
            section.classList.remove(Classes.COLLAPSED);
        });
    }
};

window.collapseAllSectionsInOrixa = (orixaId) =>
{
    const orixa = document.getElementById(orixaId);
    if (orixa)
    {
        const sections = getElements.call(orixa, Selectors.SECTION);
        sections.forEach(section =>
        {
            section.classList.add(Classes.COLLAPSED);
        });
    }
};

window.expandAllBLocksInSection = (sectionId) =>
{
    const section = document.getElementById(sectionId);
    if (section)
    {
        const blocks = getElements.call(section, Selectors.BLOCK);
        blocks.forEach(block =>
        {
            block.classList.add(Classes.EXPANDED);
        });
    }
};

window.collapseAllBlocksInSection = (sectionId) =>
{
    const section = document.getElementById(sectionId);
    if (section)
    {
        const blocks = getElements.call(section, Selectors.BLOCK);
        blocks.forEach(block =>
        {
            block.classList.remove(Classes.EXPANDED);
        });
    }
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