<?php

namespace Astroinfo\App\AstroSeek;

final class TraditionalChartParams
{
    // chiron_asp=on
    public array $ChironAspects = ["chiron_asp" => "on"];

    // send_calculation=1
    public array $EnviarCalculo = ["send_calculation" => 1];

    // narozeni_city=Curitiba%2C+Brazil
    public array $Cidade = ["narozeni_city" => "Curitiba, Brazil"];

    // narozeni_mesto_hidden=Curitiba
    public array $CidadeHidden = ["narozeni_mesto_hidden" => "Curitiba"];

    // narozeni_stat_hidden=BR
    public array $PaisHidden = ["narozeni_stat_hidden" => "BR"];

    // narozeni_podstat_kratky_hidden=
    public array $EstadoSubsiglaHidden = ["narozeni_podstat_kratky_hidden" => ""];

    // narozeni_sirka_stupne=25
    public array $LatitudeGraus = ["narozeni_sirka_stupne" => 25];

    // narozeni_sirka_minuty=26
    public array $LatitudeMinutos = ["narozeni_sirka_minuty" => 26];

    // narozeni_sirka_smer=1
    public array $LatitudeHemisferio = ["narozeni_sirka_smer" => 1];

    // narozeni_delka_stupne=49
    public array $LongitudeGraus = ["narozeni_delka_stupne" => 49];

    // narozeni_delka_minuty=16
    public array $LongitudeMinutos = ["narozeni_delka_minuty" => 16];

    // narozeni_delka_smer=1
    public array $LongitudeHemisferio = ["narozeni_delka_smer" => 1];

    // narozeni_timezone_form=auto
    public array $FusoHorarioModo = ["narozeni_timezone_form" => "auto"];

    // narozeni_timezone_dst_form=auto
    public array $HorarioVeraoModo = ["narozeni_timezone_dst_form" => "auto"];

    // house_system=regiomontanus
    public array $SistemaDeCasas = ["house_system" => "regiomontanus"];

    // aya=
    public array $Aya = ["aya" => ""];

    // terms=
    public array $Termos = ["terms" => ""];

    // house_system2=
    public array $SistemaDeCasas2 = ["house_system2" => ""];

    // hid_syzygy_check=on
    public array $SyzygyCheck = ["hid_syzygy_check" => "on"];

    // hid_uzel=1
    public array $NodoTipo = ["hid_uzel" => 1];

    // hid_uzel_check=on
    public array $NodoCheck = ["hid_uzel_check" => "on"];

    // custom_aya=
    public array $CustomAya = ["custom_aya" => ""];

    // custom_aya_zmena_smer=0
    public array $CustomAyaZmenaDirecao = ["custom_aya_zmena_smer" => 0];

    // custom_aya_zmena_stupne=00
    public array $CustomAyaZmenaGraus = ["custom_aya_zmena_stupne" => "00"];

    // custom_aya_zmena_minuty=00
    public array $CustomAyaZmenaMinutos = ["custom_aya_zmena_minuty" => "00"];

    // custom_aya_zmena_vteriny=00
    public array $CustomAyaZmenaSegundos = ["custom_aya_zmena_vteriny" => "00"];

    // custom_aya_vlastni_smer=0
    public array $CustomAyaProprioDirecao = ["custom_aya_vlastni_smer" => 0];

    // custom_aya_vlastni_stupne=00
    public array $CustomAyaProprioGraus = ["custom_aya_vlastni_stupne" => "00"];

    // custom_aya_vlastni_minuty=00
    public array $CustomAyaProprioMinutos = ["custom_aya_vlastni_minuty" => "00"];

    // custom_aya_vlastni_vteriny=00
    public array $CustomAyaProprioSegundos = ["custom_aya_vlastni_vteriny" => "00"];

    // tolerance=1
    public array $Tolerancia1 = ["tolerance" => 1];

    // &&aspekt_vypocet=whole   (na URL tem "&&", mas o parâmetro é este)
    public array $CalculoDeAspectos = ["aspekt_vypocet" => "whole"];

    // true_uzel=on
    public array $NodoVerdadeiro = ["true_uzel" => "on"];

    // hid_syzygy_asp=on
    public array $SyzygyAspects = ["hid_syzygy_asp" => "on"];

    // hid_uzel_asp=on
    public array $NodoAspects_1 = ["hid_uzel_asp" => "on"];

    // tolerance=fixed_15.0  (repete tolerance com outro valor)
    public array $Tolerancia2 = ["tolerance" => "fixed_15.0"];

    // zmena_nastaveni=1
    public array $ZmenaNastaveni_1 = ["zmena_nastaveni" => 1];

    // aktivni_tab=
    public array $AbaAtiva = ["aktivni_tab" => ""];

    // redraw_button=Redraw
    public array $BotaoRedraw = ["redraw_button" => "Redraw"];

    // hid_fortune_asp=on
    public array $FortuneAspects = ["hid_fortune_asp" => "on"];

    // hid_spirit_asp=on
    public array $SpiritAspects = ["hid_spirit_asp" => "on"];

    // hid_uzel_asp=on (repetido)
    public array $NodoAspects_2 = ["hid_uzel_asp" => "on"];

    // hid_asc_asp=on
    public array $AscAspects = ["hid_asc_asp" => "on"];

    // zmena_nastaveni=1 (repetido)
    public array $ZmenaNastaveni_2 = ["zmena_nastaveni" => 1];

    // narozeni_den=26
    public array $DiaNascimento = ["narozeni_den" => 26];

    // narozeni_mesic=12
    public array $MesNascimento = ["narozeni_mesic" => 12];

    // narozeni_rok=2025
    public array $AnoNascimento = ["narozeni_rok" => 2025];

    // narozeni_hodina=9
    public array $HoraNascimento = ["narozeni_hodina" => 9];

    // narozeni_minuta=37
    public array $MinutoNascimento = ["narozeni_minuta" => 37];

    // narozeni_sekunda=25
    public array $SegundoNascimento = ["narozeni_sekunda" => 25];

    public function toQueryArray(): array
    {
        // NOTE: Keep same order as URL
        return array_merge(
            $this->ChironAspects,
            $this->EnviarCalculo,
            $this->Cidade,
            $this->CidadeHidden,
            $this->PaisHidden,
            $this->EstadoSubsiglaHidden,

            $this->LatitudeGraus,
            $this->LatitudeMinutos,
            $this->LatitudeHemisferio,
            $this->LongitudeGraus,
            $this->LongitudeMinutos,
            $this->LongitudeHemisferio,

            $this->FusoHorarioModo,
            $this->HorarioVeraoModo,
            $this->SistemaDeCasas,

            $this->Aya,
            $this->Termos,
            $this->SistemaDeCasas2,

            $this->SyzygyCheck,
            $this->NodoTipo,
            $this->NodoCheck,

            $this->CustomAya,
            $this->CustomAyaZmenaDirecao,
            $this->CustomAyaZmenaGraus,
            $this->CustomAyaZmenaMinutos,
            $this->CustomAyaZmenaSegundos,
            $this->CustomAyaProprioDirecao,
            $this->CustomAyaProprioGraus,
            $this->CustomAyaProprioMinutos,
            $this->CustomAyaProprioSegundos,

            $this->Tolerancia1,
            $this->CalculoDeAspectos,
            $this->NodoVerdadeiro,
            $this->SyzygyAspects,
            $this->NodoAspects_1,

            $this->Tolerancia2,
            $this->ZmenaNastaveni_1,
            $this->AbaAtiva,
            $this->BotaoRedraw,
            $this->FortuneAspects,
            $this->SpiritAspects,
            $this->NodoAspects_2,
            $this->AscAspects,
            $this->ZmenaNastaveni_2,

            $this->DiaNascimento,
            $this->MesNascimento,
            $this->AnoNascimento,
            $this->HoraNascimento,
            $this->MinutoNascimento,
            $this->SegundoNascimento
        );
    }

    public function toUrl(): string
    {
        $baseUrl = 'https://horoscopes.astro-seek.com/calculate-traditional-chart/';

        $query = http_build_query(
            $this->toQueryArray(),
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        return $baseUrl . '?' . $query;
    }
}
