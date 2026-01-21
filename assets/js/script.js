/**
 * E-CIPA - Lógica Front-end
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Lógica de Cálculo de Cronograma Automático
    const inputDataPosse = document.getElementById('data_posse');
    if (inputDataPosse) {
        inputDataPosse.addEventListener('change', function() {
            const dataPosse = this.value;
            if (dataPosse) {
                calcularCronograma(dataPosse);
            }
        });
    }

    // Função para calcular cronograma via API
    async function calcularCronograma(dataPosse) {
        const formData = new FormData();
        formData.append('data_posse', dataPosse);

        try {
            const response = await fetch('../api/cronograma.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.error) {
                alert(data.error);
                return;
            }

            // Preencher campos do cronograma se existirem na tela
            atualizarCamposCronograma(data);
        } catch (error) {
            console.error('Erro ao calcular cronograma:', error);
        }
    }

    function atualizarCamposCronograma(prazos) {
        const mapping = {
            'data_inicio_inscricao': prazos.inicio_inscricoes,
            'data_fim_inscricao': prazos.fim_inscricoes,
            'data_eleicao': prazos.eleicao
        };

        for (const [id, valor] of Object.entries(mapping)) {
            const el = document.getElementById(id);
            if (el) el.value = valor;
        }

        // Exibir tabela de cronograma detalhada se existir
        const tabelaCronograma = document.getElementById('tabela-cronograma-body');
        if (tabelaCronograma) {
            tabelaCronograma.innerHTML = `
                <tr><td>Edital de Convocação</td><td>${formatarData(prazos.edital_convocacao)}</td></tr>
                <tr><td>Formação da Comissão</td><td>${formatarData(prazos.comissao_eleitoral)}</td></tr>
                <tr><td>Início das Inscrições</td><td>${formatarData(prazos.inicio_inscricoes)}</td></tr>
                <tr><td>Término das Inscrições</td><td>${formatarData(prazos.fim_inscricoes)}</td></tr>
                <tr><td>Realização da Eleição</td><td>${formatarData(prazos.eleicao)}</td></tr>
                <tr><td>Curso para Cipeiros</td><td>${formatarData(prazos.curso_cipeiros)}</td></tr>
                <tr><td>Posse</td><td>${formatarData(prazos.posse)}</td></tr>
            `;
        }
    }

    function formatarData(dataStr) {
        const partes = dataStr.split('-');
        return `${partes[2]}/${partes[1]}/${partes[0]}`;
    }

    // Máscara de CPF simples
    const inputCPF = document.getElementById('cpf');
    if (inputCPF) {
        inputCPF.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length <= 11) {
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d)/, '$1.$2');
                v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            e.target.value = v;
        });
    }
});
