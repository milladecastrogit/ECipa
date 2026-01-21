# E-CIPA - Sistema de Eleição Digital para CIPA

## 📋 Descrição
Sistema completo de eleição digital para CIPA (Comissão Interna de Prevenção de Acidentes) com suporte a votação online, votos físicos com cédula, relatórios em tempo real e autenticação com aprovação por administrador.

## ✨ Funcionalidades Implementadas

### Autenticação e Autorização
- ✅ Login com validação de status (Ativo/Pendente/Inativo)
- ✅ Registro de novo usuário (auto-approval pendente)
- ✅ 3 tipos de usuário: Administrador, Funcionário, Comissão
- ✅ Menu lateral dinâmico conforme perfil
- ✅ Logout seguro

### Gerenciamento de Eleições (Admin)
- ✅ Criar novas eleições com calendário
- ✅ Definir datas de inscrição, votação e posse
- ✅ Alterar status da eleição (Planejamento → Inscrições → Votação → Finalizada)
- ✅ Visualizar histórico de eleições

### Gestão de Usuários (Admin)
- ✅ Aprovar/rejeitar usuários pendentes
- ✅ Cadastro manual de funcionários
- ✅ Campos: Nome, CPF, Matrícula, Email, Telefone, Setor, Cargo
- ✅ Status de usuário: Ativo, Pendente, Inativo

### Votação
- ✅ Votação online com candidatos aprovados
- ✅ Bloqueio de voto duplo por eleição
- ✅ Tipos de voto: Nominal, Branco, Nulo
- ✅ Código de verificação criptografado (SHA256)
- ✅ Impedir voto se já votou via cédula

### Votos Físicos (Comissão)
- ✅ Interface dedicada para registrar votos de cédula
- ✅ Validação de CPF do eleitor
- ✅ Bloqueio se eleitor já votou online
- ✅ Contador de votos registrados por dia
- ✅ Ranking parcial de candidatos

### Acompanhamento em Tempo Real (Comissão/Admin)
- ✅ Dashboard de votação com atualização automática (30s)
- ✅ Estatísticas: Funcionários cadastrados, Candidatos, Votos total
- ✅ Breakdown: Votos online vs Votos físicos
- ✅ Ranking em tempo real dos candidatos

### Resultado Final
- ✅ Dashboard com resultado final segregado
- ✅ Votos online vs Votos físicos por candidato
- ✅ Percentuais de votação
- ✅ Destaque do vencedor(a)
- ✅ Opção de impressão/download

### Candidaturas
- ✅ Funcionários podem se candidatar
- ✅ Admin aprova/rejeita candidaturas
- ✅ Visualizar minhas candidaturas com status
- ✅ Proposta opcional

### Segurança
- ✅ Senhas criptografadas com bcrypt
- ✅ Códigos de verificação SHA256
- ✅ Validação de sessão
- ✅ Auditoria de ações
- ✅ Log de todas as operações críticas

### Documentação
- ✅ Gerar PDF de registro de candidatura
- ✅ Gerar recibo de voto com código de verificação
- ✅ Comprovantes para impressão

### Design
- ✅ Paleta de cores: #f1efe7 (bg), #009002 (verde), #007001 (verde escuro), #fbc02d (amarelo)
- ✅ Gradientes amarelos para destaque
- ✅ Layout responsivo com sidebar fixo
- ✅ Ícones do Flaticon
- ✅ Header fixo com logo
- ✅ Footer com informações de contato

## 🔐 Credenciais de Teste

### Administrador
```
Email: admin@ecipa.com.br
Senha: password
Tipo: Administrador
Status: Ativo
```

### Comissão
```
Email: comissao@ecipa.com.br
Senha: password
Tipo: Comissão
Status: Ativo
CPF: 111.111.111-11
Telefone: 11 99999-9999
```

### Novo Funcionário (Teste de Aprovação)
1. Acesse a página de registro (login.php → "Não tem conta?")
2. Preencha: Nome, Email, CPF, Telefone, Senha
3. Sistema criará usuário com status "Pendente"
4. Admin deve aprovar em "Gerenciar Usuários"
5. Depois, funcionário pode fazer login

## 📁 Estrutura de Pastas

```
e-cipa/
├── index.php                    # Página inicial pública
├── config/
│   └── conexao.php             # Configuração PDO
├── pages/
│   ├── login.php               # Login
│   ├── registro.php            # Registro novo usuário
│   ├── logout.php              # Logout
│   ├── criar-eleicao.php       # Admin: criar eleição
│   ├── eleicao.php             # Listar eleições
│   ├── votacao.php             # Interface de votação
│   ├── resultado-final.php     # Resultado com breakdown
│   ├── resultado.php           # Resultado simples
│   ├── acompanhamento-votacao.php  # Comissão: tempo real
│   ├── votos-fisicos-comissao.php # Comissão: registrar votos
│   ├── cadastro-candidato.php  # Funcionário: se candidatar
│   ├── cadastro-funcionario.php # Admin: cadastro manual
│   ├── gerenciar-usuarios.php  # Admin: aprovar usuários
│   ├── dashboard-adm.php       # Admin: dashboard
│   ├── auditoria.php           # Admin: logs
│   ├── contato.php             # Página de contato
│   └── gerar-pdf.php           # Gerar PDFs
├── api/
│   └── votar.php               # API de votação (criptografia)
├── includes/
│   ├── layout-header.php       # Header + Sidebar
│   └── layout-footer.php       # Footer
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── script.js
│   └── img/
│       └── logo e-cipa.png
└── sql/
    └── e-cipa.sql              # Schema do banco
```

## 🗄️ Banco de Dados

### Principais Tabelas
- **funcionario**: Usuários do sistema (Ativo/Pendente/Inativo)
- **eleicao**: Eleições (Planejamento/Inscrições/Votação/Finalizada)
- **candidatura**: Candidatos (Pendente/Aprovado/Rejeitado)
- **voto**: Registros de votos (Online/Físico)
- **audit_log**: Log de todas as ações

## 🚀 Como Usar

### 1. Primeiro Acesso (Admin)
```
1. Acesse http://localhost/e-cipa/pages/login.php
2. Login: admin@ecipa.com.br / password
3. Vá para "Criar Eleição"
4. Preencha dados e selecione status "Votação"
```

### 2. Registrar Novo Funcionário
```
Opção A - Self-Service (Pendente aprovação):
1. Clique em "Não tem conta?"
2. Preencha formulário
3. Admin aprova em "Gerenciar Usuários"

Opção B - Admin cadastra direto:
1. Admin vai em "Cadastro Funcionário"
2. Preencha campos
3. Funcionário pode fazer login imediatamente
```

### 3. Candidatura
```
1. Funcionário logado acessa "Ser Candidato"
2. Seleciona eleição em período de inscrição
3. Proposta é opcional
4. Admin aprova candidatura em dashboard
```

### 4. Votação Online
```
1. Eleição deve estar em status "Votação"
2. Funcionário acessa "Votação"
3. Seleciona candidato ou voto branco
4. Sistema gera código verificação
5. Recebe comprovante
```

### 5. Votos Físicos (Comissão)
```
1. Comissão logada acessa "Votos Físicos"
2. Digita CPF do eleitor
3. Seleciona candidato
4. Sistema registra e bloqueia nova votação desse CPF
5. SMS será enviado (simulado no sistema)
```

### 6. Acompanhamento em Tempo Real
```
1. Comissão acessa "Acompanhamento"
2. Página atualiza a cada 30 segundos
3. Visualiza ranking em tempo real
4. Vê breakdown online vs físico
```

## 📊 Relatórios e Documentos

### Gerar Comprovante de Candidatura
- Funcionário acessa "Ser Candidato"
- Clica no botão de impressão/PDF
- Salva documento

### Gerar Recibo de Voto
- Após votar, recebe link para recibo
- Contém: CPF, candidato, data, código verificação
- Pode imprimir para guardar

### Resultado Final
- Admin/Comissão acessa "Resultado Final"
- Imprime ou salva como PDF
- Mostra vencedor(a) destacado

## 🔒 Segurança

### Criptografia
- Senhas: bcrypt (PASSWORD_DEFAULT)
- Código verificação: SHA256(user_id + eleicao_id + timestamp + random)

### Validações
- Bloqueio de voto duplo por eleição
- Bloqueio se já votou via cédula
- Validação de CPF
- Autenticação obrigatória
- Verificação de papel de usuário

### Auditoria
- Todas as ações registradas
- Log inclui: usuário, ação, alvo, timestamp
- Página de auditoria (Admin)

## 📞 Contato

Informações de contato no sistema:
- Email: contato@ecipa.com.br
- WhatsApp: 11 98765-4321
- Telefone: 11 3333-4444

## 💡 Próximos Passos (Opcional)

- [ ] Integração real com SMS (Twilio)
- [ ] Exportar relatórios em Excel
- [ ] Gerar PDF real (TCPDF/FPDF)
- [ ] 2FA com código por SMS
- [ ] Assinatura digital
- [ ] Certificado digital para votos
- [ ] API REST completa
- [ ] Mobile app (React Native)

## 📝 Notas

- Banco de dados: MySQL/MariaDB
- Framework: PHP 7.4+
- Sem dependências externas (apenas PDO nativo)
- Banco é resetado ao importar SQL
- Telefone é campo obrigatório no cadastro

## 🛠️ Troubleshooting

**Erro "Table 'e-cipa.funcionario' doesn't exist"**
- Verifique se o SQL foi importado
- Use comando: `CREATE DATABASE ecipa; SOURCE /path/to/e-cipa.sql;`

**Login mostra "Aguardando aprovação"**
- Admin deve aprovar em "Gerenciar Usuários"
- Novo usuário começa com status "Pendente"

**Votos não aparecem em tempo real**
- Página atualiza a cada 30 segundos
- Clique em "Atualizar em Tempo Real" para forçar

---

**Desenvolvido com ❤️ para CIPA**
