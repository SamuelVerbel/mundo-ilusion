<?php
class TiendaController extends Controller
{
    public function info(): void { $this->view('tienda/info', ['title' => 'Información de la tienda']); }
}
