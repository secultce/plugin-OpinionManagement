$(document).ready(() => {
    const opinionHtml = opinion => {
        let htmlParsed = '<div class="">'
        htmlParsed += `<h3>Parecerista ${opinion.agent.name}</h3>`
        htmlParsed += `<p>Resultado da avaliação documental: ${opinion.resultString}</p>`
        for(const criteriaId in opinion.evaluationData) {
            if(criteriaId !== 'published') {
                const criteria = opinion.evaluationData[criteriaId]
                htmlParsed += `<div class="criteria-fields">`
                htmlParsed += `<h5>${criteria.label}</h5>`
                htmlParsed += `<span class="criteria-status-${criteria.evaluation === '' ? 'pending' : criteria.evaluation}"></span>`
                htmlParsed += criteria.evaluation === 'invalid' ? `<p class="evaluation-obs">${criteria['obs_items']}</p>` : ''
                htmlParsed += `</div>`
            }
        }
        htmlParsed += '</div>'
        return htmlParsed
    }

    const showOpinions = registrationId => {
        fetch(MapasCulturais.baseURL + 'opinionManagement/opinions?'+ new URLSearchParams({
            id: registrationId
        }))
            .then(response => response.json())
            .then(opinions => {
                const html = `<div>${opinions.map(opinion => opinionHtml(opinion)).join('')}</div>`;

                Swal.fire({
                    html
                })
            })
            .catch(error => {
                console.log(error)
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