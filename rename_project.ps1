$exts = @('*.php', '*.html', '*.js', '*.json', '*.md', '*.sql', '*.bat', '*.txt', '*.css')
$files = Get-ChildItem -Path '.' -Recurse -Include $exts
$count = 0
foreach ($f in $files) {
    try {
        $content = Get-Content -Path $f.FullName -Raw -ErrorAction Stop
        if ($null -ne $content) {
            $newContent = $content -creplace 'MediCure', 'Medicature'
            $newContent = $newContent -creplace 'medicure', 'medicature'
            $newContent = $newContent -creplace 'Medicure', 'Medicature'
            $newContent = $newContent -creplace 'MEDICURE', 'MEDICATURE'
            if ($content -cne $newContent) {
                Set-Content -Path $f.FullName -Value $newContent -NoNewline -Encoding UTF8
                $count++
            }
        }
    } catch {
        Write-Warning "Could not process $($f.FullName)"
    }
}
Write-Output "Updated $count files."
