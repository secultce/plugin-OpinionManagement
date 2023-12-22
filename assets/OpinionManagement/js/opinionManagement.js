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

    const showOpinionButtons = $('.showOpinion')
    for(const showOpinionButton in showOpinionButtons) {
        console.log(showOpinionButtons)
        showOpinionButtons[showOpinionButton].on('click', e => {
            showOpinions(e.target.getAttribute('data-id'))
        })
    }
})