<?php

session_start();
require_once("conexao_banco.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-do-list</title>
    <link rel="shortcut icon" type="imagex/png" href="./img/icone.png">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/style.css">

</head>

<body>

    <main>
        <button class="open-modal">Adicionar Tarefa</button>
        <dialog class="modal">

            <button class="close-modal" id="modal-btn">X</button>
            <form action="" method="post">
                Nome: <input type="text" name="campo1"><br>
                Descricao: <input type="text" name="campo2"><br>

                StatusTarefa:
                <select name="campo3">
                    <option value="pendente">Pendente</option>
                    <option value="concluida">Concluída</option>
                </select><br>

                DataCriacao: <input type="date" name="campo4"><br>
                DataConclusao: <input type="date" name="campo5"><br>

                Prioridade:
                <select name="campo6">
                    <option value="alta">Alta</option>
                    <option value="media">Média</option>
                    <option value="baixa">Baixa</option>
                </select><br>

                <input type="submit" name="ok" value="Adicionar" class="close-modal">
            </form>
        </dialog>
        <br>
        
        <form action="" method="get">
            Alterar Status:
            <select name="alterar_status">
                <option value="pendente">Pendente</option>
                <option value="concluida">Concluída</option>
            </select>
            Tarefa Nome: <input type="text" name="nome_tarefa">
            <input type="submit" name="alterar" value="Alterar Status">
        </form>

        <?php

        if (isset($_POST['ok'])) {
            $nome = filter_input(INPUT_POST, 'campo1', FILTER_SANITIZE_STRING);
            $descricao = filter_input(INPUT_POST, 'campo2', FILTER_SANITIZE_STRING);
            $statustarefa = filter_input(INPUT_POST, 'campo3', FILTER_SANITIZE_STRING) ?: 'pendente'; // Valor padrão.
            $datacriacao = filter_input(INPUT_POST, 'campo4', FILTER_SANITIZE_STRING);
            $dataconclusao = filter_input(INPUT_POST, 'campo5', FILTER_SANITIZE_STRING);
            $prioridade = filter_input(INPUT_POST, 'campo6', FILTER_SANITIZE_STRING) ?: 'media'; // Valor padrão.

            try {
                $inserir = $conn->prepare("
            INSERT INTO tarefas (nome, descricao, status_tarefa, data_criacao, data_conclusao, prioridade) 
            VALUES (:nome, :descricao, :status_tarefa, :data_criacao, :data_conclusao, :prioridade)
        ");
                $inserir->bindParam(':nome', $nome);
                $inserir->bindParam(':descricao', $descricao);
                $inserir->bindParam(':status_tarefa', $statustarefa);
                $inserir->bindParam(':data_criacao', $datacriacao);
                $inserir->bindParam(':data_conclusao', $dataconclusao);
                $inserir->bindParam(':prioridade', $prioridade);

                if ($inserir->execute()) {
                    echo "Tarefa adicionada com sucesso!";
                } else {
                    echo "Erro ao adicionar tarefa.";
                }
            } catch (PDOException $e) {
                echo "Erro ao adicionar: " . $e->getMessage();
            }
        }

        // Verifica se o formulário foi enviado para alterar o status de uma tarefa.
        if (isset($_GET['alterar'])) {
            $nome_tarefa = filter_input(INPUT_GET, 'nome_tarefa', FILTER_SANITIZE_STRING);
            $novo_status = filter_input(INPUT_GET, 'alterar_status', FILTER_SANITIZE_STRING);

            if ($nome_tarefa && $novo_status) {
                try {
                    // Verifica se a tarefa existe no banco de dados.
                    $consulta_tarefa = $conn->prepare("SELECT * FROM tarefas WHERE nome = :nome_tarefa");
                    $consulta_tarefa->bindParam(':nome_tarefa', $nome_tarefa);
                    $consulta_tarefa->execute();
                    $tarefa = $consulta_tarefa->fetch(PDO::FETCH_ASSOC);

                    if ($tarefa) {
                        // Se a tarefa existir, altera o status.
                        $alterar_status = $conn->prepare("UPDATE tarefas SET status_tarefa = :status_tarefa WHERE nome = :nome_tarefa");
                        $alterar_status->bindParam(':status_tarefa', $novo_status);
                        $alterar_status->bindParam(':nome_tarefa', $nome_tarefa);

                        if ($alterar_status->execute()) {
                            echo "Status da tarefa '{$nome_tarefa}' alterado com sucesso!";
                        } else {
                            echo "Erro ao alterar status da tarefa.";
                        }
                    } else {
                        // Caso a tarefa não exista.
                        echo "Essa tarefa não existe para ser alterada.";
                    }
                } catch (PDOException $e) {
                    echo "Erro ao alterar status: " . $e->getMessage();
                }
            }
        }

        // Verifica se uma tarefa foi solicitada para exclusão.
        if (isset($_GET['delete'])) {
            $id_tarefa = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
            if ($id_tarefa) {
                try {
                    $excluir = $conn->prepare("DELETE FROM tarefas WHERE id_tarefa = :id_tarefa");
                    $excluir->bindParam(':id_tarefa', $id_tarefa);
                    if ($excluir->execute()) {
                        echo "Tarefa excluída com sucesso!";
                    } else {
                        echo "Erro ao excluir tarefa.";
                    }
                } catch (PDOException $e) {
                    echo "Erro ao excluir: " . $e->getMessage();
                }
            }
        }

        // Consulta todas as tarefas do banco de dados.
        try {
            $consulta = $conn->query("SELECT * FROM tarefas ORDER BY data_criacao DESC");
            $tarefas = $consulta->fetchAll(PDO::FETCH_ASSOC);

            if ($tarefas) {
                echo "<table border='1'>";
                echo "<thead>
                <tr>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Status</th>
                <th>Data Criação</th>
                <th>Data Conclusão</th>
                <th>Prioridade</th>
                <th>Ações</th>
              </tr>
              </thead>";
                foreach ($tarefas as $tarefa) {
                    echo "<tr>
                    <td>{$tarefa['nome']}</td>
                    <td>{$tarefa['descricao']}</td>
                    <td class='stts'>{$tarefa['status_tarefa']}</td>
                    <td>{$tarefa['data_criacao']}</td>
                    <td>{$tarefa['data_conclusao']}</td>
                    <td>{$tarefa['prioridade']}</td>
                    <td>
                        <a href='?delete={$tarefa['id_tarefa']}' class='btn btn-delete'>Excluir</a>
                    </td>
                  </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Não há tarefas cadastradas.</p>";
            }
        } catch (PDOException $e) {
            echo "Erro ao buscar tarefas: " . $e->getMessage();
        }
        ?>
    </main>

    <script src="./assets/main.js"></script>
</body>

</html>