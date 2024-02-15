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
        <h3>Parecerista <a href="${opinion.agent.singleUrl}" target="_blank">${opinion.agent.name}</a></h3>
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
            console.log(response)
            return response.json()
        })
        .then(opinions => {
            // @todo: Talvez seja algo a ser melhorado
            if(opinions.length === 0)
                return Swal.fire({
                    icon: "info",
                    title: "Não há pareceristas",
                    text: "Não há pareceristas para esta inscrição"
                })

            const html = `<div>${opinions.map(opinion => opinionHtml(opinion)).join('')}</div>`;

            Swal.fire({
                html,
                showCloseButton: true,
                showConfirmButton: false,
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