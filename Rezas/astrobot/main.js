(function ()
{
    // Helpers
    function onlyDigits(value)
    {
        return value.replace(/\D+/g, "");
    }

    function clampLength(value, maxLen)
    {
        if (value.length > maxLen)
        {
            return value.slice(0, maxLen);
        }

        return value;
    }

    function formatDateBR(raw)
    {
        // dd/mm/yyyy (8 digits)
        var digits = clampLength(onlyDigits(raw), 8);
        var dd = digits.slice(0, 2);
        var mm = digits.slice(2, 4);
        var yyyy = digits.slice(4, 8);

        var out = dd;

        if (mm.length > 0)
        {
            out += "/" + mm;
        }

        if (yyyy.length > 0)
        {
            out += "/" + yyyy;
        }

        return out;
    }

    function formatTime(raw)
    {
        // hh:mm (4 digits)
        var digits = clampLength(onlyDigits(raw), 4);
        var hh = digits.slice(0, 2);
        var mm = digits.slice(2, 4);

        var out = hh;

        if (mm.length > 0)
        {
            out += ":" + mm;
        }

        return out;
    }

    function pad2(n)
    {
        return String(n).padStart(2, "0");
    }

    function nowDateBR()
    {
        var d = new Date();
        return pad2(d.getDate()) + "/" + pad2(d.getMonth() + 1) + "/" + d.getFullYear();
    }

    function nowTimeHM()
    {
        var d = new Date();
        return pad2(d.getHours()) + ":" + pad2(d.getMinutes());
    }

    function isValidDateBR(value)
    {
        if (!/^\d{2}\/\d{2}\/\d{4}$/.test(value))
        {
            return false;
        }

        var parts = value.split("/");
        var d = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        var y = parseInt(parts[2], 10);

        if (y < 1000 || y > 3000)
        {
            return false;
        }

        if (m < 1 || m > 12)
        {
            return false;
        }

        var maxDay = new Date(y, m, 0).getDate();
        if (d < 1 || d > maxDay)
        {
            return false;
        }

        return true;
    }

    function isValidTime(value)
    {
        if (!/^\d{2}:\d{2}$/.test(value))
        {
            return false;
        }

        var parts = value.split(":");
        var hh = parseInt(parts[0], 10);
        var mm = parseInt(parts[1], 10);

        if (hh < 0 || hh > 23)
        {
            return false;
        }

        if (mm < 0 || mm > 59)
        {
            return false;
        }

        return true;
    }

    function setInvalid(el, isInvalid)
    {
        el.setAttribute("aria-invalid", isInvalid ? "true" : "false");
    }

    function setIfEmpty(el, value)
    {
        if (el.value.trim().length === 0)
        {
            el.value = value;
        }
    }

    // Elements
    var form = document.querySelector("form");
    var errorsBox = document.getElementById("form_errors");

    var mapDate = document.getElementById("map_date");
    var mapTime = document.getElementById("map_time");

    var transitDate = document.getElementById("transit_date");
    var transitTime = document.getElementById("transit_time");

    var btnClearTransit = document.getElementById("btn_clear_transit");

    // Auto-fill defaults on load
    function applyDefaults()
    {
        setIfEmpty(mapDate, nowDateBR());
        setIfEmpty(mapTime, nowTimeHM());

        // Your birth data as default for transit
        setIfEmpty(transitDate, "07/06/1992");
        setIfEmpty(transitTime, "03:58");
    }

    applyDefaults();

    // Masks
    mapDate.addEventListener("input", function ()
    {
        mapDate.value = formatDateBR(mapDate.value);
        setInvalid(mapDate, false);
    });

    mapTime.addEventListener("input", function ()
    {
        mapTime.value = formatTime(mapTime.value);
        setInvalid(mapTime, false);
    });

    transitDate.addEventListener("input", function ()
    {
        transitDate.value = formatDateBR(transitDate.value);
        setInvalid(transitDate, false);
    });

    transitTime.addEventListener("input", function ()
    {
        transitTime.value = formatTime(transitTime.value);
        setInvalid(transitTime, false);
    });

    // Clear transit button
    btnClearTransit.addEventListener("click", function ()
    {
        transitDate.value = "";
        transitTime.value = "";

        setInvalid(transitDate, false);
        setInvalid(transitTime, false);

        if (errorsBox)
        {
            errorsBox.textContent = "";
        }

        transitDate.focus();
    });

    // Validation
    form.addEventListener("submit", function (e)
    {
        errorsBox.textContent = "";

        var errors = [];

        // Required (map)
        if (!isValidDateBR(mapDate.value))
        {
            errors.push("Data do mapa inválida. Use dd/mm/aaaa.");
            setInvalid(mapDate, true);
        }

        if (!isValidTime(mapTime.value))
        {
            errors.push("Hora do mapa inválida. Use hh:mm (00:00 a 23:59).");
            setInvalid(mapTime, true);
        }

        // Optional (transit) - if one filled, require the other
        var tDateFilled = transitDate.value.trim().length > 0;
        var tTimeFilled = transitTime.value.trim().length > 0;

        if (tDateFilled || tTimeFilled)
        {
            if (!tDateFilled || !isValidDateBR(transitDate.value))
            {
                errors.push("Data do trânsito inválida (ou faltando). Use dd/mm/aaaa.");
                setInvalid(transitDate, true);
            }

            if (!tTimeFilled || !isValidTime(transitTime.value))
            {
                errors.push("Hora do trânsito inválida (ou faltando). Use hh:mm (00:00 a 23:59).");
                setInvalid(transitTime, true);
            }
        }

        if (errors.length > 0)
        {
            e.preventDefault();
            errorsBox.textContent = errors.join(" ");
        }
    });
})();
