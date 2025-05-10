<?php

namespace App\Http\Controllers\Relatorio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelatorioSaidaController extends Controller
{
    public function index()
    {
        return view('menu.relatorio.saida-produto-relatorio');
    }

    public function search(Request $request)
    {
        try {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $nome_Produto = $request->input('nome_Produto');
            $nome_Local = $request->input('nome_Local');
            $grupo = $request->input('grupo');
            $sub_Grupo = $request->input('sub_Grupo');
            
            Log::info('Buscando saídas para relatório:', [
                'start_date' => $start_date,
                'end_date' => $end_date,
                'nome_Produto' => $nome_Produto,
                'nome_Local' => $nome_Local,
                'grupo' => $grupo,
                'sub_Grupo' => $sub_Grupo
            ]);
            
            // Validar as datas
            if (empty($start_date) || empty($end_date)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Por favor, informe o período de consulta'
                ]);
            }
            
            if (strtotime($start_date) > strtotime($end_date)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'A data inicial deve ser menor ou igual à data final'
                ]);
            }
            
            // Agrupar as saídas por data
            $query = DB::table('saida_produtos')
                ->select(
                    'saida_produtos.data_Saida',
                    DB::raw('MIN(saida_produtos.id_Saida) as id_Saida'), // Usar o menor ID como referência
                    DB::raw('SUM(saida_produtos.valor_Total) as valor_Total')
                )
                ->whereBetween('saida_produtos.data_Saida', [$start_date, $end_date])
                ->groupBy('saida_produtos.data_Saida');
            
            // Aplicar filtros adicionais se fornecidos
            if (!empty($nome_Produto) || !empty($nome_Local) || !empty($grupo) || !empty($sub_Grupo)) {
                $query->join('produtos', 'saida_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                      ->join('local_destinos', 'saida_produtos.id_Local', '=', 'local_destinos.id_Local');
                
                if (!empty($nome_Produto)) {
                    $query->where('produtos.nome_Produto', 'like', '%' . $nome_Produto . '%');
                }
                
                if (!empty($nome_Local)) {
                    $query->where('local_destinos.nome_Local', 'like', '%' . $nome_Local . '%');
                }
                
                if (!empty($grupo)) {
                    $query->where('produtos.grupo', 'like', '%' . $grupo . '%');
                }
                
                if (!empty($sub_Grupo)) {
                    $query->where('produtos.sub_Grupo', 'like', '%' . $sub_Grupo . '%');
                }
            }
            
            $saidas = $query->get();
            
            if ($saidas->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nenhuma saída encontrada para o período e filtros informados'
                ]);
            }
            
            // Formatar data para exibição no formato brasileiro e valores monetários
            foreach ($saidas as $saida) {
                if (isset($saida->data_Saida)) {
                    $data = date_create($saida->data_Saida);
                    $saida->data_Saida = date_format($data, 'd/m/Y');
                }
                
                // Formatar valor para exibição
                if (isset($saida->valor_Total)) {
                    $saida->valor_Total = number_format($saida->valor_Total, 2, ',', '.');
                }
            }
            
            Log::info('Saídas encontradas para relatório:', [
                'count' => count($saidas),
                'primeira_saida' => $saidas->first()
            ]);
            
            return response()->json([
                'status' => 'success',
                'saidas' => $saidas
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar saídas para relatório', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao buscar saídas: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function detalhes(Request $request)
    {
        try {
            $id_Saida = $request->input('id_Saida');
            $data_Saida = $request->input('data_Saida');
            
            if (empty($id_Saida) && empty($data_Saida)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID da saída ou data de saída não informados'
                ]);
            }
            
            // Buscar informações da saída específica
            $query = DB::table('saida_produtos')
                ->select(
                    'saida_produtos.*', 
                    'usuarios.nome_Usuario'
                )
                ->join('usuarios', 'saida_produtos.id_Usuario', '=', 'usuarios.id_Usuario');
                
            if (!empty($id_Saida)) {
                $query->where('saida_produtos.id_Saida', $id_Saida);
            } else {
                // Converter data para o formato do banco, assumindo que vem no formato dd/mm/yyyy
                $data_Saida_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_Saida)));
                $query->where('saida_produtos.data_Saida', $data_Saida_formatada);
            }
            
            $saida = $query->first();
            
            if (!$saida) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Saída não encontrada'
                ]);
            }
            
            // Formatar data
            if (isset($saida->data_Saida)) {
                $data = date_create($saida->data_Saida);
                $saida->data_Saida = date_format($data, 'd/m/Y');
            }
            
            // Buscar produtos dessa data ou ID
            $query = DB::table('saida_produtos')
                ->select(
                    'saida_produtos.id_Saida',
                    'local_destinos.id_Local',
                    'local_destinos.nome_Local',
                    'local_destinos.tipo_Local',
                    'produtos.cod_Produto',
                    'produtos.nome_Produto',
                    'saida_produtos.qtd_saida',
                    'produtos.tipo_Produto',
                    'produtos.preco_Custo',
                    'produtos.grupo',
                    'produtos.sub_Grupo'
                )
                ->join('produtos', 'saida_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                ->join('local_destinos', 'saida_produtos.id_Local', '=', 'local_destinos.id_Local');
                
            if (!empty($id_Saida)) {
                // Se tiver ID, buscar exatamente esse registro
                $query->where('saida_produtos.id_Saida', $id_Saida);
            } else {
                // Se tiver data, buscar todos dessa data
                $data_Saida_formatada = date('Y-m-d', strtotime(str_replace('/', '-', $data_Saida)));
                $query->where('saida_produtos.data_Saida', $data_Saida_formatada);
            }
            
            $produtos = $query->get();
            
            if ($produtos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nenhum produto encontrado para esta saída'
                ]);
            }
            
            // Formatar valores monetários
            foreach ($produtos as $produto) {
                if (isset($produto->preco_Custo)) {
                    $produto->preco_Custo = number_format($produto->preco_Custo, 2, ',', '.');
                }
            }
            
            return response()->json([
                'status' => 'success',
                'lote' => $saida,
                'produtos' => $produtos
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes do lote', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao buscar detalhes: ' . $e->getMessage()
            ], 500);
        }
    }
}