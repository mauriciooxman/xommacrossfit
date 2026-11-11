$ErrorActionPreference = 'Stop'
$demoProject = Split-Path -Parent $PSScriptRoot
$stateFile = Join-Path $demoProject 'storage/demo-processes.json'
if (!(Test-Path -LiteralPath $stateFile)) { throw 'No hay registro de esta presentación.' }
$state = Get-Content -LiteralPath $stateFile -Raw | ConvertFrom-Json
# Detiene primero el acceso público; valida PID, nombre y fecha para evitar reutilización de PID.
foreach ($entry in @($state.tunnel, $state.php)) {
    if (!$entry) { continue }
    $process = Get-Process -Id $entry.id -ErrorAction SilentlyContinue
    if (!$process) { Write-Output "Ya detenido: PID $($entry.id)"; continue }
    if ($process.ProcessName -ne $entry.name -or $process.StartTime.ToUniversalTime().ToString('o') -ne $entry.startedUtc) {
        throw "PID $($entry.id) no coincide con el registrado; no se detuvo."
    }
    Stop-Process -Id $entry.id
    Write-Output "Detenido: $($entry.name) PID $($entry.id)"
}
