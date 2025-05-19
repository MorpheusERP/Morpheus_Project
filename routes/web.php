<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\HomeController;
use App\Http\Controllers\Menu\UsuarioController;
use App\Http\Controllers\Menu\FornecedorController;
use App\Http\Controllers\Menu\LocalDestinoController;
use App\Http\Controllers\Menu\ProdutoController;
use App\Http\Controllers\Menu\SaidaProdutoController;
use App\Http\Controllers\Menu\EntradaProdutoController;
use App\Http\Controllers\Menu\RelatorioController;
use App\Http\Controllers\Menu\PerfilController;
use App\Http\Controllers\Relatorio\RelatorioUsuarioController;

// Rota inicial redirecionando para o home
Route::get('/', function () {
    return redirect()->route('login');
})->name('auth.login');

// Rotas de autenticação
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rotas de redefinição de senha
Route::get('/redefinir', [PasswordResetController::class, 'showResetForm'])->name('auth.redefinir');
Route::post('/redefinir', [PasswordResetController::class, 'reset'])->name('auth.redefinir.post');
Route::get('/novasenha', [PasswordResetController::class, 'showNovaSenhaForm'])->name('auth.novasenha');
Route::post('/novasenha', [PasswordResetController::class, 'saveNewPassword'])->name('auth.novasenha.post');

// Rotas protegidas por autenticação
Route::middleware(['auth'])->group(function () {
    // Home principal
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Perfil
    Route::prefix('perfil')->group(function () {
        Route::get('/', [PerfilController::class, 'index'])->name('menu.home.perfil');
        Route::post('/atualizar', [PerfilController::class, 'update'])->name('atualizar-perfil');
    });
    
    // Usuários
    Route::prefix('usuarios')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('menu.usuarios.usuarios');
        Route::post('/store', [UsuarioController::class, 'store'])->name('menu.usuarios.store');
        Route::get('/buscar', [UsuarioController::class, 'showBuscar'])->name('menu.usuarios.usuarios-buscar');
        Route::post('/search', [UsuarioController::class, 'search'])->name('menu.usuarios.search');
        Route::get('/editar', [UsuarioController::class, 'showEditar'])->name('menu.usuarios.usuarios-editar');
        Route::post('/update', [UsuarioController::class, 'update'])->name('menu.usuarios.usuarios-update');
    });
    
    // Fornecedor
    Route::prefix('fornecedor')->group(function () {
        Route::get('/', [FornecedorController::class, 'index'])->name('menu.fornecedor.fornecedor');
        Route::post('/store', [FornecedorController::class, 'store'])->name('menu.fornecedor.store');
        Route::get('/buscar', [FornecedorController::class, 'showBuscar'])->name('menu.fornecedor.fornecedor-buscar');
        Route::post('/search', [FornecedorController::class, 'search'])->name('menu.fornecedor.search');
        Route::get('/editar', [FornecedorController::class, 'showEditar'])->name('menu.fornecedor.fornecedor-editar');
        Route::post('/update', [FornecedorController::class, 'update'])->name('menu.fornecedor.fornecedor-update');
        Route::post('/find', [FornecedorController::class, 'find'])->name('menu.fornecedor.find');
    });

    // Local de Destino
    Route::prefix('local-destino')->group(function () {
        Route::get('/', [LocalDestinoController::class, 'index'])->name('menu.local-destino.local-destino');
        Route::get('/buscar', [LocalDestinoController::class, 'showBuscar'])->name('menu.local-destino.local-destino-buscar');
        Route::get('/editar', [LocalDestinoController::class, 'showEditar'])->name('menu.local-destino.local-destino-editar');
        Route::post('/store', [LocalDestinoController::class, 'store'])->name('menu.local-destino.store');
        Route::post('/search', [LocalDestinoController::class, 'search'])->name('menu.local-destino.search');
        Route::post('/find', [LocalDestinoController::class, 'find'])->name('menu.local-destino.find');
        Route::post('/update', [LocalDestinoController::class, 'update'])->name('menu.local-destino.local-destino-update');
        Route::delete('/delete/{id}', [LocalDestinoController::class, 'destroy']);
    });
    
    // Produtos
    Route::prefix('produtos')->group(function () {
        Route::get('/', [ProdutoController::class, 'index'])->name('menu.produtos.produtos');
        Route::post('/store', [ProdutoController::class, 'store'])->name('produto.store');
        Route::get('/buscar', [ProdutoController::class, 'showBuscar'])->name('menu.produtos.produtos-buscar');
        Route::get('/search', [ProdutoController::class, 'search'])->name('produto.search');
        Route::get('/ultimo-codigo', [ProdutoController::class, 'ultimoCodigo'])->name('ultimoCodigo');
        Route::get('/find', [ProdutoController::class, 'find'])->name('produto.find');
        Route::get('/editar', [ProdutoController::class, 'showEditar'])->name('menu.produtos.produtos-editar');
        Route::post('/update', [ProdutoController::class, 'update'])->name('produto.update');
    });
    
    // Saída de Produtos
    Route::prefix('saida-produtos')->group(function () {
        Route::get('/', [SaidaProdutoController::class, 'index'])->name('menu.saida-produtos.saida-produtos');
        Route::post('/store', [SaidaProdutoController::class, 'store'])->name('saida-produto.store');
        Route::get('/buscar', [SaidaProdutoController::class, 'showBuscar'])->name('menu.saida-produtos.saida-produtos-buscar');
        Route::post('/search', [SaidaProdutoController::class, 'search'])->name('saida-produto.search');
        Route::post('/find', [SaidaProdutoController::class, 'find'])->name('saida-produto.find');
        Route::get('/editar', [SaidaProdutoController::class, 'showEditar'])->name('menu.saida-produtos.saida-produtos-editar');
        Route::post('/update', [SaidaProdutoController::class, 'update'])->name('saida-produto.update');
        Route::post('/destroy', [SaidaProdutoController::class, 'destroy'])->name('saida-produto.destroy');
    });
    
    // Entrada de Produtos
    Route::prefix('entrada-produtos')->group(function () {
        Route::get('/', [EntradaProdutoController::class, 'index'])->name('menu.entrada-produtos.entrada-produtos');
        Route::post('/store', [EntradaProdutoController::class, 'store'])->name('entrada-produtos.store');
        Route::get('/buscar', [EntradaProdutoController::class, 'showBuscar'])->name('menu.entrada-produtos.entrada-produtos-buscar');
        Route::post('/search', [EntradaProdutoController::class, 'search'])->name('entrada-produtos.search');
        Route::post('/find', [EntradaProdutoController::class, 'find'])->name('entrada-produtos.find');
        Route::get('/editar', [EntradaProdutoController::class, 'showEditar'])->name('menu.entrada-produtos.entrada-produtos-editar');
        Route::post('/update', [EntradaProdutoController::class, 'update'])->name('entrada-produtos.update');
        Route::post('/destroy', [EntradaProdutoController::class, 'destroy'])->name('entrada-produtos.destroy');
        Route::post('/fornecedores', [EntradaProdutoController::class, 'getFornecedores'])->name('entrada-produtos.fornecedores');
        Route::post('/produtos', [EntradaProdutoController::class, 'getProdutos'])->name('entrada-produtos.produtos');
    });
    
    // Relatórios
    Route::prefix('relatorio')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])->name('menu.relatorio.relatorio');
        
        // Relatório de usuários
        Route::get('/usuarios', [RelatorioUsuarioController::class, 'index'])->name('menu.relatorio.usuarios');
        Route::post('/usuarios/search', [RelatorioUsuarioController::class, 'search'])->name('menu.relatorio.usuarios.search');
        
        // Relatório de fornecedores
        Route::get('/fornecedores', [RelatorioController::class, 'fornecedoresRelatorio'])->name('menu.relatorio.fornecedores-relatorio');
        Route::post('/fornecedores/search', [RelatorioController::class, 'searchFornecedores'])->name('menu.relatorio.fornecedores.search');
        
        // Relatório de locais
        Route::get('/locais', [RelatorioController::class, 'locaisRelatorio'])->name('menu.relatorio.locais-relatorio');
        Route::post('/locais/search', [RelatorioController::class, 'searchLocais'])->name('menu.relatorio.locais.search');
        
        // Relatório de produtos
        Route::get('/produtos', [RelatorioController::class, 'produtosRelatorio'])->name('menu.relatorio.produtos-relatorio');
        Route::post('/produtos/search', [RelatorioController::class, 'searchProdutos'])->name('menu.relatorio.produtos.search');
        
        // Relatório de entradas
        Route::get('/entradas', [RelatorioController::class, 'entradasRelatorio'])->name('menu.relatorio.entradas-relatorio');
        Route::post('/entradas/search', [RelatorioController::class, 'searchEntradas'])->name('menu.relatorio.entradas.search');
        Route::post('/entradas/detalhes', [RelatorioController::class, 'detalhesEntrada'])->name('menu.relatorio.entradas.detalhes');
        
        // Relatório de saídas
        Route::get('/saidas', [RelatorioController::class, 'saidasRelatorio'])->name('menu.relatorio.saidas-relatorio');
        Route::post('/saidas/search', [RelatorioController::class, 'searchSaidas'])->name('menu.relatorio.saidas.search');
        Route::post('/saidas/detalhes', [RelatorioController::class, 'detalhesSaidas'])->name('menu.relatorio.saidas.detalhes');
    });
});

