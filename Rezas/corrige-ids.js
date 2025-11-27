// corrige-ids.js
const fs = require("fs");
const path = require("path");

const original = "rezas.json";

// Função para normalizar o título
function gerarId(titulo)
{
    return titulo
        .normalize("NFD")                // separa acentos
        .replace(/[\u0300-\u036f]/g, "") // remove acentos
        .replace(/^[(\[{]/, "-")          // troca primeiro (, [ ou { por -
        .replace(/[()\[\]{}]/g, "")       // remove parenteses, colchetes e chaves
        .replace(/[.,;:!?'"""''«»<>@#\$%\^&\*\+=~`|\\]/g, "")
        .replace(/^-+/, "")               // remove hífens no início
        .replace(/-+$/g, "")              // remove hífens no fim
        .toLowerCase()
        .replace(/[\/\s]+/g, "-");       // troca espaço ou / por -
}

// === Criar backup incremental ===
function criarBackup(file)
{
    let i = 1;
    let backup;
    do
    {
        const num = String(i).padStart(2, "0"); // 01, 02, 03...
        backup = `${file}.${num}.bak`;
        i++;
    } while (fs.existsSync(backup));

    fs.copyFileSync(file, backup);
    console.log(`Backup criado: ${backup}`);
}

// Carrega o JSON
const data = JSON.parse(fs.readFileSync(original, "utf8"));

// Corrige os IDs dentro da chave "rezas"
for (const chave in data.rezas)
{
    data.rezas[chave] = data.rezas[chave].map(item =>
    {
        //Corrige o Titulo removendo parenteses, colchetes e chaves, e espacos desnecessarios
        item.titulo = item.titulo.replace(/[()\[\]{}]/g, "").trim();
        item.id = gerarId(item.titulo);
        return item;
    });
}

// Cria backup antes de sobrescrever
criarBackup(original);

// Salva de volta no mesmo arquivo
fs.writeFileSync(original, JSON.stringify(data, null, 2), "utf8");

console.log(`IDs corrigidos e salvos em ${original}`);
