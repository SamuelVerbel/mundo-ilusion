<?php
// Subida segura de imágenes: valida tipo real (no solo la extensión), tamaño y guarda con nombre aleatorio.
class Upload
{
    const MAX = 2097152; // 2 MB
    const TIPOS = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public static function imagen(array $f): string
    {
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new RuntimeException('No se pudo subir una de las imágenes.');
        if ($f['size'] > self::MAX) throw new RuntimeException('Cada imagen debe pesar máximo 2 MB.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset(self::TIPOS[$mime])) throw new RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        $nombre = bin2hex(random_bytes(10)) . '.' . self::TIPOS[$mime];
        if (!move_uploaded_file($f['tmp_name'], ROOT_PATH . '/public/uploads/productos/' . $nombre)) throw new RuntimeException('No se pudo guardar la imagen.');
        return $nombre;
    }
    public static function borrar(string $ruta): void
    {
        if (!str_contains($ruta, '/') && is_file($f = ROOT_PATH . '/public/uploads/productos/' . basename($ruta))) @unlink($f);   // las de demo (img/...) no se borran
    }
    /** Normaliza $_FILES['campo'] múltiple a una lista de archivos. */
    public static function lista(string $campo): array
    {
        $out = []; $f = $_FILES[$campo] ?? null;
        if (!$f || !is_array($f['name'])) return $out;
        foreach ($f['name'] as $i => $n) if ($f['error'][$i] !== UPLOAD_ERR_NO_FILE) $out[] = ['name' => $n, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
        return $out;
    }
}
