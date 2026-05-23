param(
    [string]$BaseUrl = 'https://justadudewhohacks.github.io/face-api.js/models',
    [string]$OutDir = 'public/assets/models'
)

if(-not (Test-Path $OutDir)){
    New-Item -ItemType Directory -Path $OutDir -Force | Out-Null
}

$manifests = @(
    'tiny_face_detector_model-weights_manifest.json',
    'face_landmark_68_model-weights_manifest.json',
    'face_recognition_model-weights_manifest.json'
)

Write-Host "Downloading models from: $BaseUrl" -ForegroundColor Cyan

foreach($m in $manifests){
    try{
        $manifestUrl = "$BaseUrl/$m"
        $manifestPath = Join-Path $OutDir $m
        Write-Host "Fetching manifest: $manifestUrl"
        Invoke-WebRequest -Uri $manifestUrl -OutFile $manifestPath -UseBasicParsing -ErrorAction Stop

        $content = Get-Content $manifestPath -Raw
        $matches = [regex]::Matches($content, '[\w\-/\.]+\.bin') | ForEach-Object { $_.Value } | Select-Object -Unique

        foreach($b in $matches){
            # Resolve relative path against manifest URL
            $binUri = (New-Object System.Uri((New-Object System.Uri($manifestUrl)), $b)).AbsoluteUri
            $fileName = Split-Path $b -Leaf
            $dest = Join-Path $OutDir $fileName
            if(Test-Path $dest){
                Write-Host "Skipped (exists): $fileName"
                continue
            }
            Write-Host "Downloading: $binUri -> $dest"
            Invoke-WebRequest -Uri $binUri -OutFile $dest -UseBasicParsing -ErrorAction Stop
        }
    }catch{
        Write-Host "Failed to download manifest or bins for $m: $_" -ForegroundColor Yellow
    }
}

Write-Host "Done. Models placed in: $OutDir" -ForegroundColor Green
