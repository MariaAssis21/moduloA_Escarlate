# 🚚 SENN AI-X - Plataforma de Gestão Logística de Última Milha

![Status](https://img.shields.io/badge/Status-Conclu%C3%ADdo-success)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-10.4+-4479A1?logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?logo=javascript&logoColor=black)

O **SENN AI-X** é uma plataforma web completa desenvolvida para otimizar a logística de última milha. O sistema integra gestão de frotas, despacho de pedidos, rastreamento em tempo real e um módulo robusto de Inteligência Artificial (IA) para previsão de demanda, análise de riscos, chatbot e roteirização otimizada.

---

## 🚀 Tecnologias Utilizadas

*   **Backend:** PHP 8.0+ (Vanilla, arquitetura de APIs RESTful)
*   **Banco de Dados:** MySQL / MariaDB (via XAMPP)
*   **Frontend:** HTML5, CSS3 (Design Responsivo Mobile-First), JavaScript (ES6+)
*   **Biblioteca de Gráficos:** Chart.js
*   **Biblioteca de Mapas:** Leaflet.js com OpenStreetMap

---

## ⚙️ Como Instalar e Executar

### Pré-requisitos
*   Ter o [XAMPP](https://www.apachefriends.org/pt_br/index.html) (ou WAMP/MAMP) instalado no computador.
*   Navegador web moderno (Chrome, Edge, Firefox).
*   Git (opcional, para clonar).

### Passo a Passo para Execução

1. **Obtenha o projeto:**
   Clone o repositório ou baixe o arquivo `.zip` e extraia.
   ```bash
   git clone https://github.com/seu-usuario/senn-ai-x.git
Mova para o servidor local:
Coloque a pasta do projeto dentro do diretório htdocs do seu XAMPP.
Caminho exemplo: C:\xampp\htdocs\senn-ai-x

Inicie os serviços:
Abra o XAMPP Control Panel e clique em Start nos módulos Apache e MySQL.

Configure o Banco de Dados:

Acesse o phpMyAdmin no navegador: http://localhost/phpmyadmin.

Clique em Novo no menu lateral esquerdo e crie um banco de dados chamado exatamente: senn_logistica (com collation utf8mb4_unicode_ci).

Selecione o banco criado, vá na aba Importar, clique em Escolher arquivo e selecione o arquivo database.sql que está na raiz do projeto.

Clique em Executar (no final da página). O banco será criado com todas as tabelas e dados de teste.

Verifique a Conexão:

Abra o arquivo config/database.php.

Certifique-se de que as constantes DB_USER e DB_PASS estão de acordo com o seu XAMPP (padrão: usuário root e senha vazia).

Acesse o Sistema:

Abra o navegador e acesse: http://localhost/senn-ai-x/

Contas Padrão (criadas pelo banco de dados):

Admin: admin@senn.com | Senha: 123456

Gestor: gestor@senn.com | Senha: 123456

Despachante: despachante@senn.com | Senha: 123456

Motorista: joao.motorista@senn.com | Senha: 123456

🧠 Documentação de Inteligência Artificial e Roteirização
Para atender aos requisitos da competição, detalhamos abaixo a abordagem técnica adotada.

1. Modelo de IA Utilizado
Abordagem: Inteligência Artificial Determinística Baseada em Regras (Rule-Based System) e Estatística.
Integração: Toda a IA foi construída localmente no backend em PHP, sem dependência de APIs externas pagas (como OpenAI, Gemini ou Hugging Face). Essa escolha garante que o sistema funcione 100% offline, com latência ultrabaixa e sem custos de requisição, ideal para ambientes locais (XAMPP).

2. Endpoints de IA e Prompts Utilizados
Módulo IA	Endpoint	Descrição / Lógica (Prompt)
Previsão de Demanda (RF08)	api/ia_previsao.php	Lógica: O sistema realiza uma query SQL (WEEKDAY) para descobrir o histórico de pedidos por dia da semana. A "IA" calcula a média diária e aplica um algoritmo de variação aleatória (10%) para cima ou para baixo, simulando a volatilidade do mercado para prever os próximos 7 e 30 dias.
Detecção de Risco (RF09)	api/ia_risco.php	Lógica: Utiliza um sistema de Score (pontuação) de 0 a 100%. Regras: Prioridade Alta (+30 pts), Janela de entrega curta < 2h (+25 pts), Carga pesada > 100kg (+20 pts), Prazo expirando em menos de 3h (+40 pts). Se o pedido passar de 80 pts, a IA aciona um alerta no painel.
Análise Inteligente (RF12)	api/ia_insights.php	Lógica: Utiliza Expressões Regulares (Regex) em PHP para fazer NLP (Processamento de Linguagem Natural) simples. A IA extrai a última palavra do endereço (ex: "Paulista", "Augusta") para agrupar os pedidos por região e gerar insights textuais automáticos, como "A região X teve o maior volume de entregas".
Assistente Virtual (RF13)	api/ia_chat.php	Lógica: O chatbot interpreta a intenção do usuário usando preg_match (Regex).
Prompts (Intenções) configurados:
- Frota: "veículo", "carro", "caminhão"
- Motorista: "motorista", "condutor", "cnh"
- Pedidos: "total", "pendente", "atrasado"
- Status: Detecta o código PED-XXXX para buscar no banco.
Se não reconhecer, usa o fallback: "Por favor, contate o suporte técnico".
3. Abordagem Adotada para a Roteirização (RF05)
Para solucionar o problema de otimização de rotas, foi implementado o algoritmo heurístico Nearest Neighbor (Vizinho Mais Próximo), que é uma solução clássica e eficiente para o Problema do Caixeiro Viajante (TSP).

Endpoint: api/ia_rotas.php.

Funcionamento: A IA recebe a localização (Latitude/Longitude) do veículo. Ela calcula a distância entre o veículo e todos os pedidos pendentes usando a Fórmula de Haversine (que calcula a distância real na curvatura da Terra). Ela seleciona o pedido mais próximo, vai para ele, e repete o cálculo a partir dali, traçando a rota de menor distância e menor custo até finalizar todos os pedidos.

Resultado: A rota é salva no banco de dados (coluna ordem_rota) e enviada para o aplicativo do motorista na ordem exata de entrega (Parada 1, 2, 3...).

🛡️ Requisitos Não Funcionais Atendidos
Segurança (RNF01): Proteção contra SQL Injection usando PDO com Prepared Statements. Senhas protegidas usando criptografia de mão única password_hash. Proteção de sessão e bloqueio de acesso via rotas.

LGPD (RNF08): Termo de consentimento obrigatório na tela de cadastro. Dados de localização no POD (Proof of Delivery) são coletados apenas com autorização do dispositivo (GPS).

Tempo Real (RNF04) & Fallback (RNF06): O rastreamento e os alertas utilizam setInterval (Polling) no JavaScript. Se a API do mapa (OpenStreetMap) ou a biblioteca falharem, o código conta com blocos try/catch que exibem mensagens amigáveis na tela (Modo Fallback), impedindo que o sistema quebre.

Acessibilidade (RNF03): Layout responsivo (Mobile-First para o motorista) e navegação por teclado (indicadores de focus-visible).

Backup (RNF05): A tela de "Gestão de Usuários" (Admin) possui um botão que gera e baixa o arquivo .sql completo do banco de dados.

👨‍💻 Equipe Desenvolvedora
Escarlate

Projeto desenvolvido para a competição Dev Experience
