<?php
session_start();
require_once '../config/conexao.php';

// Define a página atual para o menu
$currentPage = 'cadastro-candidato';

$pageTitle = 'Cadastrar Candidato';
$mensagem = '';
$tipo_mensagem = '';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Para Funcionário, redireciona para candidatar-se.php
if ($_SESSION['user_tipo'] === 'Funcionario') {
    header("Location: candidatar-se.php");
    exit;
}

// Verifica se é Admin ou Comissão
if (!in_array($_SESSION['user_tipo'], ['Administrador', 'Comissao'])) {
    header("Location: ../index.php");
    exit;
}

// Processa o formulário de cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = intval($_POST['funcionario_id']);
    $eleicao_id = intval($_POST['eleicao_id']);
    $resumo = trim($_POST['resumo']);

    // Validações
    if (empty($funcionario_id) || empty($eleicao_id) || empty($resumo)) {
        $mensagem = 'Todos os campos são obrigatórios!';
        $tipo_mensagem = 'erro';
    } else if (strlen($resumo) > 300) {
        $mensagem = 'O resumo não pode exceder 300 caracteres!';
        $tipo_mensagem = 'erro';
    } else {
        // Verifica se o funcionário já é candidato nesta eleição
        $stmt = $pdo->prepare("
            SELECT id FROM candidatura 
            WHERE id_funcionario = :funcionario_id AND id_eleicao = :eleicao_id
        ");
        $stmt->execute([
            ':funcionario_id' => $funcionario_id,
            ':eleicao_id' => $eleicao_id
        ]);

        if ($stmt->fetch()) {
            $mensagem = 'Este funcionário já é candidato nesta eleição!';
            $tipo_mensagem = 'erro';
        } else {
            // Processa upload de foto
            $foto_path = '';
            if (!empty($_FILES['foto']['name'])) {
                $arquivo = $_FILES['foto'];
                $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];

                if (!in_array($arquivo['type'], $tipos_permitidos)) {
                    $mensagem = 'Tipo de arquivo inválido. Apenas JPG, PNG e GIF são permitidos!';
                    $tipo_mensagem = 'erro';
                } else if ($arquivo['size'] > 5 * 1024 * 1024) {
                    $mensagem = 'O arquivo é muito grande. Máximo 5MB!';
                    $tipo_mensagem = 'erro';
                } else {
                    // Cria diretório se não existir
                    $dir = '../assets/img/candidatos';
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }

                    // Gera nome do arquivo
                    $ext = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
                    $nome_arquivo = 'candidato_' . $funcionario_id . '_' . time() . '.' . $ext;
                    $caminho = $dir . '/' . $nome_arquivo;

                    if (move_uploaded_file($arquivo['tmp_name'], $caminho)) {
                        $foto_path = 'assets/img/candidatos/' . $nome_arquivo;
                    } else {
                        $mensagem = 'Erro ao fazer upload da foto!';
                        $tipo_mensagem = 'erro';
                    }
                }
            }

            // Se não teve erro no upload, insere no banco
            if (empty($mensagem)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO candidatura (id_funcionario, id_eleicao, foto_path, proposta, status, data_candidatura)
                        VALUES (:funcionario_id, :eleicao_id, :foto_path, :proposta, 'Pendente', NOW())
                    ");
                    $stmt->execute([
                        ':funcionario_id' => $funcionario_id,
                        ':eleicao_id' => $eleicao_id,
                        ':foto_path' => $foto_path,
                        ':proposta' => $resumo
                    ]);

                    // Auditoria
                    $stmt = $pdo->prepare("
                        INSERT INTO audit_log (usuario_id, acao, descricao, created_at)
                        VALUES (:usuario_id, 'CANDIDATO_CADASTRADO', :descricao, NOW())
                    ");
                    $stmt->execute([
                        ':usuario_id' => $_SESSION['user_id'],
                        ':descricao' => "Candidato cadastrado para a eleição ID {$eleicao_id}"
                    ]);

                    $mensagem = 'Candidato cadastrado com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } catch (Exception $e) {
                    $mensagem = 'Erro ao cadastrar candidato!';
                    $tipo_mensagem = 'erro';
                }
            }
        }
    }
}

// Buscar funcionários ativos
$stmt = $pdo->prepare("
    SELECT id, nome, matricula, cpf FROM funcionario 
    WHERE status = 'Ativo' 
    ORDER BY nome ASC
");
$stmt->execute();
$funcionarios = $stmt->fetchAll(PDO::FETCH_OBJ);

// Buscar eleições ativas
$stmt = $pdo->prepare("
    SELECT id, titulo, data_eleicao FROM eleicao
    WHERE status IN ('Inscricoes', 'Votacao')
    ORDER BY data_eleicao DESC
");
$stmt->execute();
$eleicoes = $stmt->fetchAll(PDO::FETCH_OBJ);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Candidato - E-CIPA</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1efe7;
            color: #333;
        }

        .page-title {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #009002;
        }

        .page-title h1 {
            color: #009002;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .page-title p {
            color: #666;
            font-size: 14px;
        }

        .form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #009002;
            box-shadow: 0 0 0 3px rgba(0, 144, 2, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            max-height: 200px;
        }

        .char-counter {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .foto-preview-container {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .foto-preview-box {
            width: 150px;
            height: 150px;
            border: 2px dashed #e5e7eb;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9f9f9;
            overflow: hidden;
        }

        .foto-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .foto-preview-text {
            color: #999;
            font-size: 12px;
            text-align: center;
        }

        .form-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-submit {
            flex: 1;
            padding: 12px 24px;
            background: linear-gradient(135deg, #009002 0%, #007001 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 144, 2, 0.3);
        }

        .btn-cancel {
            flex: 1;
            padding: 12px 24px;
            background: #e5e7eb;
            color: #333;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #d1d5db;
        }

        .mensagem {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .mensagem.sucesso {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .mensagem.erro {
            background-color: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #666;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }

            .foto-preview-container {
                flex-direction: column;
            }

            .foto-preview-box {
                width: 100%;
            }

            .form-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/layout-header.php'; ?>

    <main>
        <div class="page-title">
            <h1>Cadastrar Candidato</h1>
            <p>Registre um novo candidato para uma eleição</p>
        </div>

        <div class="form-container">
            <?php if (!empty($mensagem)): ?>
                <div class="mensagem <?php echo $tipo_mensagem; ?>">
                    <?php echo htmlspecialchars($mensagem); ?>
                </div>
            <?php endif; ?>

            <div class="info-box">
                ℹ️ Preencha todos os campos abaixo para cadastrar um novo candidato. O funcionário deve estar ativo no sistema.
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="funcionario_id">Funcionário *</label>
                    <select id="funcionario_id" name="funcionario_id" required>
                        <option value="">-- Selecione um funcionário --</option>
                        <?php foreach ($funcionarios as $func): ?>
                            <option value="<?php echo $func->id; ?>">
                                <?php echo htmlspecialchars($func->nome); ?> (<?php echo htmlspecialchars($func->matricula); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="eleicao_id">Eleição *</label>
                    <select id="eleicao_id" name="eleicao_id" required>
                        <option value="">-- Selecione uma eleição --</option>
                        <?php foreach ($eleicoes as $eleicao): ?>
                            <option value="<?php echo $eleicao->id; ?>">
                                <?php echo htmlspecialchars($eleicao->titulo); ?> (<?php echo date('d/m/Y', strtotime($eleicao->data_eleicao)); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Foto do Candidato</label>
                    <div class="foto-preview-container">
                        <div class="foto-preview-box">
                            <img id="preview-img" style="display: none;">
                            <div id="preview-text" class="foto-preview-text">Sem foto</div>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" id="foto" name="foto" accept="image/*">
                            <p style="font-size: 12px; color: #666; margin-top: 8px;">
                                Formatos aceitos: JPG, PNG, GIF (máximo 5MB)
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resumo">Resumo do Perfil *</label>
                    <textarea id="resumo" name="resumo" required maxlength="300" placeholder="Digite o resumo do perfil (máximo 300 caracteres)"></textarea>
                    <div class="char-counter">
                        <span id="contador">0</span>/300 caracteres
                    </div>
                </div>

                <div class="form-buttons">
                    <button type="submit" class="btn-submit">✓ Cadastrar</button>
                    <button type="button" class="btn-cancel" onclick="history.back()">✕ Cancelar</button>
                </div>
            </form>
        </div>
    </main>

    <footer style="margin-left: 250px; padding: 20px; text-align: center; color: #999; font-size: 12px; margin-top: 40px;">
        <p>&copy; 2024 E-CIPA - Sistema de Votação Digital. Todos os direitos reservados.</p>
    </footer>

    <script>
        // Preview de foto
        document.getElementById('foto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const previewImg = document.getElementById('preview-img');
                    const previewText = document.getElementById('preview-text');
                    previewImg.src = event.target.result;
                    previewImg.style.display = 'block';
                    previewText.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Contador de caracteres
        document.getElementById('resumo').addEventListener('keyup', function() {
            document.getElementById('contador').textContent = this.value.length;
        });
    </script>
</body>
</html>

