# 5. Diagrama de Classes

As classes ficam em `src/`. As páginas `.php` da raiz funcionam como controladores: recebem o formulário, chamam as classes e montam a tela.

```mermaid
classDiagram
    direction LR

    class Database {
        -PDO pdo$
        +conexao()$ PDO
        +executar(sql, parametros)$ PDOStatement
    }

    class Usuario {
        +cadastrar(nome, email, senha, perfil)$ array
        +autenticar(email, senha)$ array?
        +buscarPorEmail(email)$ array?
        +buscarPorId(id)$ array?
    }

    class PontoTuristico {
        +listar(filtros)$ array
        +buscar(id)$ array?
        +cidades()$ array
        +validar(dados)$ array
        +salvar(dados, guiaId, id)$ int
        +excluir(id, guiaId)$ bool
    }

    class Evento {
        +listarFuturos(filtros)$ array
        +listarDoOrganizador(orgId)$ array
        +noPeriodo(inicio, fim)$ array
        +buscar(id)$ array?
        +validar(dados)$ array
        +salvar(dados, orgId, id)$ int
        +excluir(id, orgId)$ bool
    }

    class Avaliacao {
        +avaliar(usuarioId, pontoId, nota, comentario)$ array
        +doPonto(pontoId)$ array
        +doUsuarioNoPonto(usuarioId, pontoId)$ array?
    }

    class Roteiro {
        +validarPerfil(dados)$ array
        +gerar(perfil, ia)$ array
        +salvar(usuarioId, perfil, roteiro)$ int
        +listarDoUsuario(usuarioId)$ array
        +buscarComItens(id, usuarioId)$ array?
        +excluir(id, usuarioId)$ bool
    }

    class AssistenteIA {
        -string chave
        -string modelo
        -int timeout
        -bool verificarSsl
        +daConfig()$ AssistenteIA
        +montarRoteiro(perfil, pontos, eventos) array
        +montarPrompt(perfil, pontos, eventos) string
        -chamarGemini(prompt) string
        +interpretarResposta(texto, perfil, pontos, eventos)$ array
    }

    class PlanejadorLocal {
        +MINUTOS_POR_RITMO$ array
        +montar(perfil, pontos, eventos)$ array
        -preencherDia(candidatos, cidade, base, data, limite)$ array
        -pontuar(ponto, perfil)$ float
        -dica(ponto)$ string
    }

    class GeminiAPI {
        <<externo>>
        POST generateContent
    }

    Usuario ..> Database
    PontoTuristico ..> Database
    Evento ..> Database
    Avaliacao ..> Database
    Avaliacao ..> PontoTuristico : verifica se existe
    Roteiro ..> Database
    Roteiro ..> PontoTuristico : lê pontos
    Roteiro ..> Evento : lê eventos do período
    Roteiro --> AssistenteIA : 1º tenta
    Roteiro --> PlanejadorLocal : plano B
    AssistenteIA ..> GeminiAPI : HTTPS
    AssistenteIA ..> PlanejadorLocal : usa MINUTOS_POR_RITMO
```

## Responsabilidades

| Classe | Responsabilidade |
|---|---|
| `Database` | Conexão única (Singleton) com o MySQL via PDO; todas as consultas usam *prepared statements*. |
| `Usuario` | Cadastro com `password_hash`, login com `password_verify`. |
| `PontoTuristico` | CRUD e listagem com nota média (`AVG`) e filtros. Só o guia dono edita/exclui. |
| `Evento` | CRUD, agenda futura e busca de eventos dentro do período da viagem. |
| `Avaliacao` | Cria ou atualiza (`ON DUPLICATE KEY UPDATE`) a avaliação do turista. |
| `Roteiro` | Orquestra a geração: valida o perfil, busca dados, chama a IA, cai no plano B se der erro, grava em transação. |
| `AssistenteIA` | Monta o *prompt*, chama o Gemini e **valida** a resposta (descarta IDs inventados, pontos repetidos, eventos fora de data). |
| `PlanejadorLocal` | Plano B determinístico: pontua por interesse/nota/orçamento, agrupa por cidade e respeita o ritmo. |

## Padrões usados

- **Singleton** — `Database::conexao()`.
- **Strategy (simplificado)** — `Roteiro::gerar()` escolhe entre duas estratégias de geração (IA ou local) com a mesma saída.
- **Injeção de dependência** — `Roteiro::gerar($perfil, $ia)` aceita outro `AssistenteIA`, o que facilita os testes.
