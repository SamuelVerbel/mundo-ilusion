<?php
class Usuario
{
    public static function find(int $id): ?array { return Database::one('SELECT * FROM usuario WHERE id_usuario = ?', [$id]); }
    public static function porCorreo(string $c): ?array { return Database::one('SELECT * FROM usuario WHERE correo = ?', [mb_strtolower($c)]); }
    public static function correoTomado(string $c, int $excepto = 0): bool { return (bool) Database::val('SELECT 1 FROM usuario WHERE correo = ? AND id_usuario <> ?', [mb_strtolower($c), $excepto]); }
    public static function documentoTomado(string $d, int $excepto = 0): bool { return (bool) Database::val('SELECT 1 FROM usuario WHERE documento = ? AND id_usuario <> ?', [$d, $excepto]); }

    public static function crear(array $d, string $rol = 'cliente'): int
    {
        return Database::insert(
            'INSERT INTO usuario (documento, nombre, correo, telefono, password_hash, rol, fecha_aceptacion_politica) VALUES (?,?,?,?,?,?,?)',
            [$d['documento'], $d['nombre'], mb_strtolower($d['correo']), $d['telefono'], password_hash($d['password'], PASSWORD_DEFAULT), $rol, $rol === 'cliente' ? date('Y-m-d H:i:s') : null]);
    }
    public static function actualizarPerfil(int $id, string $nombre, string $telefono, string $correo): void
    {
        Database::exec('UPDATE usuario SET nombre = ?, telefono = ?, correo = ? WHERE id_usuario = ?', [$nombre, $telefono, mb_strtolower($correo), $id]);
    }
    public static function cambiarPassword(int $id, string $plain): void
    {
        Database::exec('UPDATE usuario SET password_hash = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?', [password_hash($plain, PASSWORD_DEFAULT), $id]);
    }
    public static function registrarAcceso(int $id): void { Database::exec('UPDATE usuario SET ultimo_acceso = NOW(), intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?', [$id]); }

    /** Cuenta fallo de login; al 5.º intento bloquea 15 minutos (RNF06). Devuelve true si quedó bloqueada. */
    public static function registrarFallo(array $u): bool
    {
        $n = (int) $u['intentos_fallidos'] + 1;
        if ($n >= 5) { Database::exec('UPDATE usuario SET intentos_fallidos = 0, bloqueado_hasta = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id_usuario = ?', [$u['id_usuario']]); return true; }
        Database::exec('UPDATE usuario SET intentos_fallidos = ? WHERE id_usuario = ?', [$n, $u['id_usuario']]);
        return false;
    }
    public static function estaBloqueado(array $u): bool { return !empty($u['bloqueado_hasta']) && strtotime($u['bloqueado_hasta']) > time(); }

    // ---- Gestión de empleados (solo administrador) ----
    public static function empleados(): array { return Database::all("SELECT * FROM usuario WHERE rol = 'empleado' ORDER BY estado, nombre"); }
    public static function empleado(int $id): ?array { return Database::one("SELECT * FROM usuario WHERE id_usuario = ? AND rol = 'empleado'", [$id]); }
    public static function cambiarEstado(int $id, string $estado): void { Database::exec("UPDATE usuario SET estado = ? WHERE id_usuario = ? AND rol = 'empleado'", [$estado, $id]); }
    public static function actualizarEmpleado(int $id, string $doc, string $nombre, string $correo, string $tel): void
    {
        Database::exec("UPDATE usuario SET documento = ?, nombre = ?, correo = ?, telefono = ? WHERE id_usuario = ? AND rol = 'empleado'", [$doc, $nombre, mb_strtolower($correo), $tel, $id]);
    }
    /** Contraseña temporal legible (sin caracteres confusos) que cumple la política: 10 caracteres con letras y números. */
    public static function passwordTemporal(): string
    {
        $l = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ'; $n = '23456789'; $s = '';
        for ($i = 0; $i < 7; $i++) $s .= $l[random_int(0, strlen($l) - 1)];
        for ($i = 0; $i < 3; $i++) $s .= $n[random_int(0, strlen($n) - 1)];
        return str_shuffle($s);
    }
}
