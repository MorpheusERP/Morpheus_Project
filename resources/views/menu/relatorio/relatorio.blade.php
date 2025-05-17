@extends('layouts.app')

@section('title', 'Relatórios')
@section('header-title', 'Relatórios')

@section('content')
    <main class="main-container">
        <div class="grid-container">
            <button id="entrada_produtos" onclick="window.location.href='{{ route('menu.relatorio.entradas-relatorio') }}'">
                <img src="{{ asset('images/relatorio/relatorio-entrada-produto.png') }}" alt="Entrada Produtos">
                <span>Entrada Produtos</span>
            </button>
            <button id="saida_produtos" onclick="window.location.href='{{ route('menu.relatorio.saidas-relatorio') }}'">
                <img src="{{ asset('images/relatorio/relatorio-saida-produto.png') }}" alt="Saída Produtos">
                <span>Saída Produtos</span>
            </button>
            @if ((Auth::check() && Auth::user()->tipo_Usuario == 'admin') || session('user_type') == 'admin')
                <button id="local_destino" onclick="window.location.href='{{ route('menu.relatorio.locais-relatorio') }}'">
                    <img src="{{ asset('images/relatorio/relatorio-local-destino.png') }}" alt="Local Destino">
                    <span>Locais de destino</span>
                </button>
                <button id="produtos" onclick="window.location.href='{{ route('menu.relatorio.produtos-relatorio') }}'">
                    <img src="{{ asset('images/relatorio/relatorio-produto.png') }}" alt="Produtos">
                    <span>Produtos</span>
                </button>
                <button id="cadastro_usuario" onclick="window.location.href='{{ route('menu.relatorio.usuarios') }}'">
                    <img src="{{ asset('images/relatorio/relatorio-usuario.png') }}" alt="Usuários">
                    <span>Usuários</span>
                </button>
            @endif
            <button id="fornecedor" onclick="window.location.href='{{ route('menu.relatorio.fornecedores-relatorio') }}'">
                <img src="{{ asset('images/relatorio/relatorio-fornecedor.png') }}" alt="Fornecedores">
                <span>Fornecedores</span>
            </button>
        </div>
    </main>
@endsection

@section('footer')
    <button class="back-button" onclick="window.location.href='{{ route('home') }}'">
        <img src="{{ asset('images/relatorio/seta.svg') }}" alt="Voltar">
        <span>Sair</span>
    </button>
@endsection

@push('styles')
    @vite(['resources/css/menu/relatorio/relatorio.css'])
@endpush