$(document).ready(() => {
    const opinionHtml = opinion => {
        console.log(opinion)
        let htmlParsed = '<div class="opinion">'
        htmlParsed += `<div class="evaluation-title">
            <h3>Parecerista <a href="${opinion.agent.singleUrl}" target="_blank">${opinion.agent.name}</a></h3>
            <label for="chk-collapse"><div class="collapsible"></div></label>
            <p>Resultado da avaliação documental:<a href="${opinion.singleUrl}" class="criteria-status-${opinion.result < 0 ? 'invalid' : 'valid'}"></a></p>
        </div>
        <input type="checkbox" id="chk-collapse">`
        for(const criteriaId in opinion.evaluationData) {
            if(criteriaId !== 'published') {
                const criteria = opinion.evaluationData[criteriaId]
                htmlParsed += `<div class="criteria-fields">`
                htmlParsed += `<h5>${criteria.label}</h5>`
                htmlParsed += `<p class="criteria-status-${criteria.evaluation === '' ? 'pending' : criteria.evaluation}"></p>`
                htmlParsed += criteria.evaluation === 'invalid' ? `<p class="opinion-evaluation-obs">${criteria['obs_items']}</p>` : ''
                htmlParsed += `</div>`
            }
        }
        htmlParsed += '</div>'
        return htmlParsed
    }

    const showOpinions = registrationId => {
        fetch(MapasCulturais.baseURL + 'opinionManagement/opinions?'+ new URLSearchParams({
            id: registrationId
        }), {
            headers: {
                'Accept': 'application/json'
            }
        })
            .then(response => {
                console.log(response)
                return response.json()
            })
            .then(opinions => {
                console.log(opinions)
                const html = `<div>${opinions.map(opinion => opinionHtml(opinion)).join('')}</div>`;

                Swal.fire({
                    html
                })
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Alconteceu um problema!",
                    footer: `<code style="font-size:8px">${error}</code>`
                })
            })
    }

    // Promise criada para aguardar os elementos serem carregados na página
    const waitLoadRegistrationList = () => {
        return new Promise(resolve => {
            const selector = '.showOpinion'

            if(document.querySelectorAll(selector).length > 0) {
                return resolve(document.querySelectorAll(selector))
            }
        })
    }

    // Esse observer observando mudanças no DOM da tabela de inscritos e chama a função que atribui o callback ao evento de clique aos botões de visualizar parecer
    const observer = new MutationObserver(mutations => {
        if(document.querySelectorAll('.showOpinion').length > 0) {
            waitLoadRegistrationList().then(showOpinionButtons => {
                for(const arrayIndex of showOpinionButtons.keys()) {

                    showOpinionButtons[arrayIndex].onclick = e => {
                        showOpinions(e.target.getAttribute('data-id'))
                    }
                }
            })
        }

        // @todo: Melhorar a validação para remover o MutationObserver caso todos os inscritos já carregaram na paǵina
        if(document.querySelectorAll('.showOpinion').length % 50 !== 0) {
            observer.disconnect()
        }
    })
    observer.observe(document.querySelector('#registrations-table'), {
        childList: true,
        subtree: true
    })
})