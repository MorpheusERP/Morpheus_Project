<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTabelasTable extends Migration
{
    public function up()
    {
        // Tabela dos Usuários
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_Usuario');
            $table->string('nivel_Usuario', 11);
            $table->string('nome_Usuario', 120);
            $table->string('sobrenome', 120)->nullable();
            $table->string('funcao', 120);
            $table->string('email', 120);
            $table->string('tipo_Usuario', 50);
            $table->string('senha', 60);
            $table->timestamps();
        });

        // Tabela dos Fornecedores
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id('id_Fornecedor');
            $table->string('razao_Social', 120);
            $table->string('nome_Fantasia', 120)->nullable();
            $table->string('apelido', 120)->nullable();
            $table->string('grupo', 120)->nullable();
            $table->string('sub_Grupo', 120)->nullable();
            $table->string('observacao', 120)->nullable();
            $table->timestamps();
        });

        // Tabela dos Produtos
        Schema::create('produtos', function (Blueprint $table) {
            $table->id('id_Produto');
            $table->unsignedInteger('cod_Produto')->unique();
            $table->binary('imagem')->nullable();
            $table->string('nome_Produto', 120)->unique();
            $table->string('tipo_Produto', 120);
            $table->integer('cod_Barras')->nullable();
            $table->double('preco_Custo');
            $table->double('preco_Venda')->nullable();
            $table->string('grupo', 15);
            $table->string('sub_Grupo', 100)->nullable();
            $table->string('observacao', 120)->nullable();
            $table->timestamps();
        });

        // Tabela dos Locais de Destino
        Schema::create('local_destinos', function (Blueprint $table) {
            $table->id('id_Local');
            $table->string('nome_Local', 34)->unique();
            $table->string('tipo_Local', 34);
            $table->string('observacao', 34)->nullable();
            $table->timestamps();
        });

        // Tabela dos Estoques
        Schema::create('estoques', function (Blueprint $table) {
            $table->id('id_Estoque');
            $table->unsignedInteger('cod_Produto');
            $table->float('qtd_Estoque')->nullable();
        
            $table->foreign('cod_Produto')->references('cod_Produto')->on('produtos');
            $table->timestamps();
        });

        // Tabela de Entradas de Produtos
        Schema::create('entrada_produtos', function (Blueprint $table) {
            $table->id('id_Entrada');
            $table->unsignedBigInteger('id_Usuario');
            $table->string('nome_Usuario', 120);
            $table->unsignedBigInteger('id_Fornecedor');
            $table->string('razao_Social', 120);
            $table->unsignedInteger('cod_Produto');
            $table->string('nome_Produto', 120);
            $table->unsignedBigInteger('id_Estoque');
            $table->float('qtd_Entrada');
            $table->double('preco_Custo');
            $table->float('preco_Venda')->nullable();
            $table->double('valor_Total')->nullable();
            $table->date('data_Entrada')->nullable();
        
            $table->foreign('id_Usuario')->references('id_Usuario')->on('usuarios');
            $table->foreign('id_Fornecedor')->references('id_Fornecedor')->on('fornecedores');
            $table->foreign('id_Estoque')->references('id_Estoque')->on('estoques');
            $table->foreign('cod_Produto')->references('cod_Produto')->on('produtos');
            $table->timestamps();
        });

        // Tabela de Saídas de Produtos
        Schema::create('saida_produtos', function (Blueprint $table) {
            $table->id('id_Saida');
            $table->binary('imagem')->nullable();
            $table->unsignedBigInteger('id_Usuario');
            $table->string('nome_Usuario', 120);
            $table->unsignedInteger('cod_Produto');
            $table->string('nome_Produto', 120);
            $table->double('preco_Custo');
            $table->unsignedBigInteger('id_Local');
            $table->string('nome_Local', 34);
            $table->unsignedBigInteger('id_Estoque');
            $table->double('qtd_saida');
            $table->double('valor_Total')->nullable();
            $table->string('observacao', 120)->nullable();
            $table->date('data_Saida')->nullable();
        
            $table->foreign('id_Usuario')->references('id_Usuario')->on('usuarios');
            $table->foreign('id_Local')->references('id_Local')->on('local_destinos');
            $table->foreign('id_Estoque')->references('id_Estoque')->on('estoques');
            $table->foreign('cod_Produto')->references('cod_Produto')->on('produtos');        
            $table->timestamps();
        });

        // Tabela de Sessões 
        // Esta tabela é utilizada para armazenar informações de sessão do usuário
        // e é criada automaticamente pelo Laravel quando o sistema de autenticação é utilizado.
        
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('saida_produtos');
        Schema::dropIfExists('entrada_produtos');
        Schema::dropIfExists('estoques');
        Schema::dropIfExists('local_destinos');
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('fornecedores');
        Schema::dropIfExists('usuarios');
    }
}
