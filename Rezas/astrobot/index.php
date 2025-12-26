<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AstroInfo</title>

    <link rel="stylesheet" href="index.css">
</head>

<body>
    <main class="page">
        <section class="card">
            <header class="header">
                <h1>AstroInfo</h1>
                <p>Preencha os dados do mapa. Trânsito é opcional.</p>
            </header>

            <form class="form" action="calculate.php" method="post" novalidate>
                <fieldset class="fieldset">
                    <legend>Dados do mapa</legend>

                    <div class="field">
                        <label for="map_date">Data</label>
                        <input
                            id="map_date"
                            name="map_date"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="dd/mm/aaaa"
                            required>
                        <small class="hint" id="map_date_hint">Formato: 26/12/2025</small>
                    </div>

                    <div class="field">
                        <label for="map_time">Hora</label>
                        <input
                            id="map_time"
                            name="map_time"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="hh:mm"
                            required>
                        <small class="hint" id="map_time_hint">Formato: 08:23</small>
                    </div>
                </fieldset>

                <fieldset class="fieldset">
                    <legend>Dados do trânsito (opcional)</legend>

                    <div class="field">
                        <label for="transit_date">Data</label>
                        <input
                            id="transit_date"
                            name="transit_date"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="dd/mm/aaaa">
                        <small class="hint">Deixe em branco se não for usar.</small>
                    </div>

                    <div class="field">
                        <label for="transit_time">Hora</label>
                        <input
                            id="transit_time"
                            name="transit_time"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            placeholder="hh:mm">
                        <small class="hint">Deixe em branco se não for usar.</small>
                    </div>
                </fieldset>

                <div class="actions">
                    <button class="btn" type="submit">Calcular</button>

                    <button class="btn btn-secondary" type="button" id="btn_clear_transit">
                        Limpar trânsito
                    </button>
                </div>

                <div class="errors" aria-live="polite" id="form_errors"></div>
            </form>
        </section>
    </main>

    <script src="index.js"></script>
</body>

</html>