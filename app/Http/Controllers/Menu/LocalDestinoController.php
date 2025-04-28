<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocalDestinoController extends Controller
{
    public function index()
    {
        return view('menu.local-destino.local-destino');
    }

    public function showBuscar()
    {
        return view('menu.local-destino.local-destino-buscar');
    }

    public function showEditar()
    {
        return view('menu.local-destino.local-destino-editar');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nome_Local' => 'required|string|max:34',
                'tipo_Local' => 'required|string|max:34',
                'observacao' => 'nullable|string|max:34',
            ]);

            $existingLocal = DB::table('local_destinos')
                ->where('nome_Local', $validated['nome_Local'])
                ->first();

            if ($existingLocal) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Este local de destino já está cadastrado no sistema.'
                ]);
            }

            DB::table('local_destinos')->insert([
                'nome_Local' => $validated['nome_Local'],
                'tipo_Local' => $validated['tipo_Local'],
                'observacao' => $validated['observacao'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Local de destino cadastrado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar local de destino: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            // Log search request for debugging
            \Log::info('Local destino search request:', $request->all());
            
            $nome_Local = $request->input('nome_Local');
            $tipo_Local = $request->input('tipo_Local');
            
            $query = DB::table('local_destinos');

            // Apply filters based on input
            if (!empty($nome_Local)) {
                $query->where('nome_Local', 'LIKE', '%' . $nome_Local . '%');
            }
            
            if (!empty($tipo_Local)) {
                $query->where('tipo_Local', $tipo_Local);
            }

            $query->orderBy('nome_Local', 'asc');

            $locais = $query->get();
            
            \Log::info('Local destino search results count: ' . $locais->count());

            return response()->json([
                'status' => $locais->count() > 0 ? 'sucesso' : 'erro',
                'mensagem' => $locais->count() > 0 ? 'Locais encontrados' : 'Nenhum local de destino encontrado com os critérios informados',
                'resultados' => $locais
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar local de destino: ' . $e->getMessage());

            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a busca: ' . $e->getMessage(),
                'resultados' => []
            ]);
        }
    }

    public function find(Request $request)
    {
        try {
            $id = $request->id_Local;
            
            $local = DB::table('local_destinos')
                ->where('id_Local', $id)
                ->first();

            if (!$local) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Local de destino não encontrado.'
                ]);
            }

            return response()->json([
                'status' => 'sucesso',
                'resultados' => [$local]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar local de destino por ID: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao buscar o local de destino: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_Local' => 'required|integer',
                'nome_Local' => 'required|string|max:34',
                'tipo_Local' => 'required|string|max:34',
                'observacao' => 'nullable|string|max:34',
            ]);

            $local = DB::table('local_destinos')
                ->where('id_Local', $validated['id_Local'])
                ->first();

            if (!$local) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Local de destino não encontrado.'
                ]);
            }

            if ($local->nome_Local !== $validated['nome_Local']) {
                $duplicate = DB::table('local_destinos')
                    ->where('nome_Local', $validated['nome_Local'])
                    ->where('id_Local', '!=', $validated['id_Local'])
                    ->first();

                if ($duplicate) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Já existe outro local de destino com este nome.'
                    ]);
                }
            }

            DB::table('local_destinos')
                ->where('id_Local', $validated['id_Local'])
                ->update([
                    'nome_Local' => $validated['nome_Local'],
                    'tipo_Local' => $validated['tipo_Local'],
                    'observacao' => $validated['observacao'] ?? null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Local de destino atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar local de destino: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a atualização: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $local = DB::table('local_destinos')
                ->where('id_Local', $id)
                ->first();

            if (!$local) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Local de destino não encontrado.'
                ]);
            }

            DB::table('local_destinos')
                ->where('id_Local', $id)
                ->delete();

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Local de destino excluído com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir local de destino: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir o local de destino: ' . $e->getMessage()
            ]);
        }
    }
}
