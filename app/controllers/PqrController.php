<?php
class PqrController extends Controller
{
    public function form(): void { $this->view('pqr/form', ['title' => 'Peticiones, quejas y sugerencias', 'mias' => Database::all('SELECT * FROM pqr WHERE id_usuario = ? ORDER BY id_pqr DESC', [Auth::id()])]); }
    public function guardar(): void
    {
        $tipo = $this->post('tipo'); $asunto = $this->post('asunto'); $msg = $this->post('mensaje');
        $v = (new Validator())->in('tipo', $tipo, ['peticion', 'queja', 'reclamo', 'sugerencia'], 'Selecciona el tipo.')
            ->required('asunto', $asunto, 'El asunto')->max('asunto', $asunto, 150, 'El asunto')->required('mensaje', $msg, 'El mensaje')->max('mensaje', $msg, 2000, 'El mensaje');
        if ($v->fails()) $this->back($v->errors, 'pqr');
        Pqr::crear(Auth::id(), $tipo, $asunto, $msg);
        flash('success', 'Recibimos tu mensaje. Te responderemos por correo.');
        redirect('pqr');
    }
}
