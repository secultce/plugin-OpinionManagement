const handleChkCollapseChange = target => {
    const chkCollapses = $('.chk-collapse')
    for(let i= 0;i < chkCollapses.length; i++) {
        if (chkCollapses[i] === target) continue
        chkCollapses[i].checked = false
    }
}

const opinionHtml = opinion => {
    let htmlParsed = '<div class="opinion">'
    htmlParsed += `<div class="evaluation-title">
        <h3>
            Parecerista
            ${opinion.agent.singleUrl ?
                '<a href="'+opinion.agent.singleUrl+'" target="_blank">'+opinion.agent.name+'</a>' :
                opinion.agent.name
            }
        </h3>
        <label for="chk-collapse-${opinion.id}"><div class="collapsible"></div></label>
        <p>Resultado da avaliação documental:<a href="${opinion.singleUrl}" class="criteria-status-${opinion.result < 0 ? 'invalid' : 'valid'}"></a></p>
    </div>
    <input type="checkbox" id="chk-collapse-${opinion.id}" class="chk-collapse" name="chk-collapse" onchange="handleChkCollapseChange(this)">`
    for(const criteriaId in opinion.evaluationData) {
        const criteria = opinion.evaluationData[criteriaId]
        criteria.obs_items = criteria.obs_items?.replace('\n','<br>')
        criteria.obs = criteria.obs?.replace('\n','<br>')

        htmlParsed += `<div class="criteria-fields">`
        htmlParsed += `<h5>${criteria.label}</h5>`
        htmlParsed += `<p class="criteria-status-${criteria.evaluation === '' ? 'pending' : criteria.evaluation}"></p>`
        // htmlParsed += criteria.evaluation === 'invalid' ? `<p class="opinion-evaluation-obs">${criteria['obs_items']}</p>` : ''
        htmlParsed += `<p class="opinion-evaluation-obs">${criteria['obs_items']}</p>`
        htmlParsed += `<p class="opinion-evaluation-obs">${criteria['obs']}</p>`
        htmlParsed += `</div>`
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
            if(response.redirected) throw new Error('Guest')
            if (!response.ok) throw new Error(response.statusText)
            return response.json()
        })
        .then(opinions => {
            // @todo: Em versões futuras fazer alteração para mostrar quem são os pareceristas com pendências na avaliação
            if(opinions.length === 0)
                return Swal.fire({
                    title: "Não há avaliações!",
                    text: "As avaliações desta inscrição ainda não foram iniciadas."
                })

            const html = `<div>${opinions.map(opinion => opinionHtml(opinion)).join('')}</div>`;

            Swal.fire({
                html,
                showCloseButton: true,
                showConfirmButton: false,
            })
        })
        .catch(error => {

            let { message } = error
            if(error.message === 'Forbidden') message = 'Você não tem permissão para acessar este recurso.'
            if(error.message === 'Guest') message = 'É necessário estar autenticado.'

            errorAlert(message)
        })
}

const publishOpinions = target => {
    const opportunityId = target.getAttribute('data-id');

    Swal.fire({
        title: "Essa ação é irreversível!",
        html: "Ao clicar em <strong>'Publicar'</strong> você está publicando os pareceres para a visualização dos proponentes. Isso não pode ser desfeito.",
        showConfirmButton: true,
        showCloseButton: false,
        showCancelButton: true,
        confirmButtonText: 'Publicar',
        cancelButtonText: 'Cancelar',
    })
        .then(result => {
            if(result.isConfirmed)
                fetch(MapasCulturais.baseURL + 'opinionManagement/publishOpinions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id: opportunityId,
                    }),
                })
                    .then(response => {
                        if(response.redirected) throw new Error('Guest')
                        if (!response.ok) throw new Error(response.statusText)
                        return response.json()
                    })
                    .then(response => {
                        console.log(response)
                        Swal.fire({
                            title: "Pareceres publicados com sucesso!",
                            text: "Os pareceres desta inscrição agora encontram-se publicados.",
                            showConfirmButton: false,
                            showCloseButton: true,
                        })
                    })
                    .catch(error => {
                        let { message } = error
                        if(error.message === 'Forbidden') message = 'Você não tem permissão para acessar este recurso.'
                        if(error.message === 'Guest') message = 'É necessário estar autenticado.'

                        errorAlert(message)
                    })
        })
}

const errorAlert = message => {
    Swal.fire({
        title: "Oops...",
        text: "Aconteceu um problema!",
        footer: `<code style="font-size:11px; color:#c93">${message}</code>`,
        showConfirmButton: false,
        showCloseButton: true,
    })
}
