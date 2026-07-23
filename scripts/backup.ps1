# backup.ps1 — Gestor de copias de seguridad

$ruta = Read-Host "Ruta del archivo o carpeta que quieres borrar"

if (-not (Test-Path $ruta)) {
    Write-Host "La ruta no existe." -ForegroundColor Red
    exit 1
}

$esCarpeta = (Get-Item $ruta).PSIsContainer

if ($esCarpeta) {
    Write-Host "Es una carpeta. Se borrara todo su contenido." -ForegroundColor Yellow
} else {
    Write-Host "Es un archivo. Se borrara." -ForegroundColor Yellow
}

$guardar = @()
while ($true) {
    $archivo = Read-Host "Archivo que quieres conservar (deja vacio para terminar)"
    if ($archivo -eq "") { break }
    if (Test-Path $archivo) {
        $guardar += $archivo
    } else {
        Write-Host "No existe: $archivo" -ForegroundColor Red
    }
}

if ($guardar.Count -gt 0) {
    $destino = Read-Host "Carpeta de destino para la copia de seguridad"
    New-Item -ItemType Directory -Path $destino -Force | Out-Null
    foreach ($item in $guardar) {
        Copy-Item -Path $item -Destination $destino -Recurse -Force
        Write-Host "Copiado: $item -> $destino" -ForegroundColor Green
    }
}

$confirmacion = Read-Host "Escribe BORRAR para eliminar '$ruta'"
if ($confirmacion -eq "BORRAR") {
    Remove-Item -Path $ruta -Recurse -Force
    Write-Host "Eliminado: $ruta" -ForegroundColor Green
} else {
    Write-Host "Cancelado." -ForegroundColor Yellow
}
