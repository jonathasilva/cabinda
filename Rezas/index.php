<?php
// Path to JSON file
$jsonPath = __DIR__ . '/rezas.json';

// Load JSON content
$jsonContent = @file_get_contents($jsonPath);

// Try to decode JSON
$data = null;

if ($jsonContent !== false)
{
    $data = json_decode($jsonContent, true);
}

// Helper to safely escape output
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// Helper to capitalize first char (basic)
function capitalizeFirst(string $str): string
{
    if ($str === '')
    {
        return '';
    }

    return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rezas de Cabinda - Cantos Yorubá</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="orixa-colors.css">
</head>

<body>

    <header>
        <h1>Rezas de Cabinda</h1>
    </header>

    <div id="container">
        <?php if ($data === null) : ?>
            <div class="error">
                Erro ao carregar rezas: não foi possível ler ou decodificar o arquivo JSON.
            </div>
        <?php else : ?>

            <?php foreach ($data['rezas'] as $orixaKey => $secoes) : ?>
                <div class="orixa collapsed orixa-<?php echo e(strtolower($orixaKey)); ?>">
                    <div class="orixa-header">
                        <h1><?php echo e(capitalizeFirst($orixaKey)); ?></h1>
                        <img src="chevron.svg" alt="" class="chevron">
                    </div>

                    <div class="orixa-content">
                        <?php foreach ($secoes as $secao) : ?>
                            <section class="collapsed" id="<?php echo e($secao['id'] ?? ''); ?>">
                                <h2>
                                    <?php echo e($secao['titulo'] ?? ''); ?>
                                    <img src="chevron.svg" alt="" class="chevron">
                                </h2>

                                <div class="section-content">

                                    <?php if (!empty($secao['cantos']) && is_array($secao['cantos'])) : ?>

                                        <?php foreach ($secao['cantos'] as $canto) : ?>
                                            <div class="block">
                                                <div class="block-header">
                                                    <div class="block-content">

                                                        <div class="yo-text">
                                                            <?php
                                                            $sing = $canto['sing'] ?? '';
                                                            $yoruba = $canto['yoruba'] ?? '';
                                                            ?>

                                                            <?php if (!empty($sing) && $sing !== 'none') : ?>
                                                                <span class="sing-text">
                                                                    (<?php echo e($sing); ?>):
                                                                </span>
                                                                <?php echo ' ' . e($yoruba); ?>
                                                            <?php else : ?>
                                                                <?php echo e($yoruba); ?>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="trans-text">
                                                            <?php echo e($canto['transliteracao'] ?? ''); ?>
                                                        </div>
                                                    </div>

                                                    <button class="toggle-btn" aria-label="Expandir tradução">
                                                        <img src="chevron.svg" alt="" class="chevron">
                                                    </button>
                                                </div>

                                                <div class="pt-text">
                                                    <?php echo e($canto['traducao'] ?? ''); ?>

                                                    <?php
                                                    $notes = $canto['notetexts'] ?? [];
                                                    if (is_array($notes) && count($notes) > 0) :
                                                        foreach ($notes as $note) :
                                                    ?>
                                                            <span class="note-text">
                                                                <?php echo e($note); ?>
                                                            </span>
                                                    <?php
                                                        endforeach;
                                                    endif;
                                                    ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                        <blockquote>
                                            <h3>Transliteração Completa</h3>

                                            <?php foreach ($secao['cantos'] as $canto) : ?>
                                                <div class="quotetrans-text">
                                                    <?php echo e($canto['transliteracao'] ?? ''); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </blockquote>

                                    <?php endif; ?>

                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>
    </div>

    <script src="script.js"></script>

</body>

</html>