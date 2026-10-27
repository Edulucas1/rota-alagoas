# 8. Roteiro do Pitch (5 minutos) e da Demo

## Divisão do tempo

| Tempo | Quem | O que falar / mostrar |
|---|---|---|
| **0:00 – 0:40** | Eduardo | **Problema.** "Quem vem a Alagoas por 3 dias encontra a informação espalhada em Instagram, blogs e agências. Não sabe o que cabe no tempo, nem o que combina com quem está viajando. E o guia local e o produtor cultural não têm onde divulgar." |
| **0:40 – 1:10** | José Eduardo | **Solução.** "O Rota Alagoas reúne pontos turísticos, eventos e avaliações num só lugar. E tem um assistente de IA que monta o roteiro pelo seu perfil e pelo seu tempo." Mostrar a tela inicial. |
| **1:10 – 3:10** | Eduardo (tela) + Matheus (narra) | **Demo ao vivo** (ver abaixo). |
| **3:10 – 3:50** | Kelvin | **Como foi feito.** PHP + MySQL no XAMPP, GitHub, Trello com 3 sprints. Mostrar rapidamente o modelo ER e o quadro do Trello. |
| **3:50 – 4:30** | José Wellington | **Qualidade.** 17 casos de teste, 17 testes automatizados, 6 bugs encontrados e corrigidos. "A IA só pode sugerir lugares que existem no banco; se ela inventar, o sistema descarta. E se a IA cair, o plano B entrega o roteiro do mesmo jeito." |
| **4:30 – 5:00** | Eduardo | **Fechamento.** Próximos passos (mapa, fotos, compartilhar no WhatsApp) e frase final: "Rota Alagoas: o seu roteiro, do seu jeito, com o que Alagoas tem de melhor." |

## Demo ao vivo (2 minutos)

Deixe tudo aberto **antes** de começar: XAMPP ligado, navegador já logado como `ana@rota.al`.

1. **Pontos turísticos** → filtrar "Praias" em Maceió → abrir "Piscinas naturais da Pajuçara" → mostrar nota e avaliações. *(20 s)*
2. **Montar roteiro** → 3 dias, chegando amanhã, hospedado em Maceió, interesses Praias + Cultura + Gastronomia, Família, Moderado. → **Gerar**. *(40 s — enquanto carrega, o narrador explica o que entra na IA)*
3. Mostrar o roteiro: selo **✦ Gerado por IA**, horários, dicas, evento no dia certo. *(30 s)*
4. **Plano B ao vivo** (opcional, se der tempo): "E se a IA cair?" → apagar a chave no `config.php` (ou desligar o Wi-Fi) → gerar de novo → aviso amarelo, roteiro entregue. *(30 s)*

## Plano de contingência

| Se acontecer… | Faça |
|---|---|
| Internet do laboratório cair | Gere o roteiro mesmo assim: o plano B responde. Transforme em argumento: "É exatamente para isso que existe o plano B." |
| Gemini demorar | O *timeout* é 30 s. Enquanto isso, o narrador fala da entrada/saída da IA. |
| XAMPP não subir | Tenha um vídeo de 1 min da demo gravado no celular e os prints de `docs/prototipo/`. |
| Banco vazio | Reimporte `database/rota_alagoas.sql` no phpMyAdmin (10 s). |

## Perguntas prováveis da banca

- **"E se a IA inventar um lugar?"** — O prompt manda usar só IDs do banco, e o `AssistenteIA::interpretarResposta()` descarta qualquer ID que não exista. Tem teste automatizado para isso (CT-07).
- **"Onde fica a chave da API?"** — Em `config/config.php`, que está no `.gitignore`. No GitHub só vai o `config.example.php` com a chave vazia.
- **"Por que Gemini?"** — Cota gratuita, resposta em JSON nativa (`responseMimeType`) e é trocável: o modelo está no arquivo de configuração.
- **"Os dados são reais?"** — Os pontos turísticos são reais; custos e durações são aproximados; os eventos são fictícios para a demonstração. Guias e organizadores reais cadastrariam os seus.
