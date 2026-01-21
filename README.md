# E-CIPA - Sistema de Eleição Digital

Plataforma responsiva para gestão e realização de eleições da CIPA, desenvolvida com foco em segurança, transparência e conformidade com a NR-5.

## 🚀 Tecnologias Utilizadas

- **Front-end:** HTML5, CSS3 (Design Responsivo), JavaScript (Vanilla)
- **Back-end:** PHP 8.x
- **Banco de Dados:** MySQL / MariaDB
- **Estilização:** Paleta de cores Dax Oil, Background Hexagonal com transparência.

## 📂 Estrutura do Projeto

- `api/`: Endpoints para lógica de negócio (Auth, Cronograma, etc).
- `assets/`: Recursos estáticos (CSS, JS, Imagens).
- `config/`: Configurações de sistema e conexão com banco de dados.
- `pages/`: Páginas da interface do usuário.
- `sql/`: Scripts de criação do banco de dados.

## 🛠️ Instalação (Localhost/XAMPP)

1. Certifique-se de ter o **XAMPP** instalado e os serviços Apache e MySQL ativos.
2. Copie a pasta `e-cipa` para o diretório `htdocs` do seu XAMPP.
3. Acesse o **phpMyAdmin** (`http://localhost/phpmyadmin`).
4. Crie um novo banco de dados chamado `ecipa`.
5. Importe o arquivo `sql/ecipa.sql` para o banco criado.
6. Acesse o sistema via navegador: `http://localhost/e-cipa`.

## 🔐 Acesso Inicial (Admin)

- **E-mail:** `admin@ecipa.com.br`
- **Senha:** `password`

## ✨ Funcionalidades Principais

- **Cálculo Automático de Cronograma:** Baseado na data da posse, o sistema calcula todos os prazos legais da NR-5.
- **Gestão de Candidatos:** Cadastro simplificado com upload de fotos e propostas.
- **Votação Digital:** Interface intuitiva e segura para os funcionários.
- **Auditoria:** Log de ações para garantir a integridade do processo eleitoral.
- **Design Responsivo:** Adaptado para dispositivos móveis e desktops.

---
Desenvolvido para **Dax Oil** - Sistema E-CIPA
