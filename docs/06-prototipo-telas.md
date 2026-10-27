# 6. Protótipo de Telas

Fluxo de navegação e capturas das telas do sistema (as imagens estão em `docs/prototipo/`).

```mermaid
flowchart TD
    I[Início] --> P[Pontos turísticos]
    I --> E[Eventos]
    I --> R{Logado como turista?}
    P --> PD[Detalhe do ponto + avaliação]
    R -- não --> L[Login / Cadastro]
    R -- sim --> F[Montar roteiro]
    L --> F
    F -->|Gerar| IA{IA respondeu?}
    IA -- sim --> RG[Roteiro gerado ✦ IA]
    IA -- não --> RB[Roteiro gerado - plano B]
    RG --> MR[Meus roteiros]
    RB --> MR
    L -->|guia / organizador| PN[Meu painel]
    PN --> FP[Cadastro de ponto]
    PN --> FE[Cadastro de evento]
```

## Telas

### 1. Início
Destaques mais bem avaliados, próximos eventos e o botão principal "Montar meu roteiro".
![Início](prototipo/01-inicio.png)

### 2. Pontos turísticos (RF04)
Busca por nome, filtro por cidade e categoria, nota média.
![Pontos](prototipo/02-pontos.png)

### 3. Detalhe do ponto + avaliação (RF06)
![Detalhe](prototipo/03-ponto-detalhe.png)

### 4. Agenda de eventos (RF05)
![Eventos](prototipo/04-eventos.png)

### 5. Login
![Login](prototipo/05-login.png)

### 6. Montar roteiro — formulário do assistente de IA (RF07)
![Montar roteiro](prototipo/06-montar-roteiro.png)

### 7. Roteiro gerado (RF07, RF08)
Linha do tempo por dia; selo indica se veio da IA ou do plano B.
![Roteiro](prototipo/07-roteiro-gerado.png)

### 8. Meus roteiros (RF08)
![Meus roteiros](prototipo/08-meus-roteiros.png)

### 9. Painel do guia (RF02)
![Painel](prototipo/09-painel-guia.png)

### 10. Cadastro de ponto turístico (RF02)
![Cadastro ponto](prototipo/10-cadastro-ponto.png)

### 11. Cadastro de evento (RF03)
![Cadastro evento](prototipo/11-cadastro-evento.png)

### 12. Versão celular (RNF03)
![Celular](prototipo/12-celular-roteiro.png)

## Guia visual

| Elemento | Valor |
|---|---|
| Cor primária (verde-mar) | `#0f766e` |
| Cor de destaque (laranja, ação de IA) | `#ea6a2a` |
| Fundo | `#f6f4ef` (areia) |
| Texto | `#0f172a` |
| Fonte | Fonte do sistema (Segoe UI no Windows, Roboto no Android) |
| Ícone de IA | ✦ — sempre que algo envolve o assistente |
