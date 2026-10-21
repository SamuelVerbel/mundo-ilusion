<?php
// Validaciones de formularios en el servidor (nunca confiar solo en el navegador).
class Validator
{
    public array $errors = [];
    public function required(string $f, $v, string $label): self { if (trim((string) $v) === '') $this->errors[$f] = "$label es obligatorio."; return $this; }
    public function email(string $f, $v, string $label = 'El correo'): self { if (!isset($this->errors[$f]) && !filter_var($v, FILTER_VALIDATE_EMAIL)) $this->errors[$f] = "$label no es válido."; return $this; }
    public function min(string $f, $v, int $n, string $label): self { if (!isset($this->errors[$f]) && mb_strlen((string) $v) < $n) $this->errors[$f] = "$label debe tener al menos $n caracteres."; return $this; }
    public function max(string $f, $v, int $n, string $label): self { if (!isset($this->errors[$f]) && mb_strlen((string) $v) > $n) $this->errors[$f] = "$label no puede superar $n caracteres."; return $this; }
    public function regex(string $f, $v, string $re, string $msg): self { if (!isset($this->errors[$f]) && !preg_match($re, (string) $v)) $this->errors[$f] = $msg; return $this; }
    public function in(string $f, $v, array $ok, string $msg): self { if (!isset($this->errors[$f]) && !in_array($v, $ok, true)) $this->errors[$f] = $msg; return $this; }
    public function custom(string $f, bool $cond, string $msg): self { if (!isset($this->errors[$f]) && !$cond) $this->errors[$f] = $msg; return $this; }
    public function password(string $f, $v): self {
        if (isset($this->errors[$f])) return $this;
        if (mb_strlen((string) $v) < 8 || !preg_match('/[A-Za-z]/', (string) $v) || !preg_match('/\d/', (string) $v)) $this->errors[$f] = 'La contraseña debe tener mínimo 8 caracteres, con letras y números.';
        return $this;
    }
    public function fails(): bool { return !empty($this->errors); }
    public function first(): string { return $this->errors ? reset($this->errors) : ''; }
}
