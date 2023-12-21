<td>
    <button
        class="btn-primary showOpinion"
        data-id="{{reg.id}}"
    >Pareceres</button>
</td>

<script>
$(document).ready(() => {
    const opinionHtml = opinion => {
        let parsed = '<div class="parecerista">'
        parsed += `<h3>Parecerista ${opinion.agent.name}</h3>`
        parsed += `<p>Resultado da avaliação documental: ${opinion.resultString}</p>`
        parsed += `<div></div>`
        parsed += '</div>'
        return parsed
    }

    const showOpinions = registrationId => {
        fetch(MapasCulturais.baseURL + 'opinionManagement/opinions?'+ new URLSearchParams({
            id: registrationId
        }))
            .then(response => response.json())
            .then(opinions => {
                Swal.fire({
                    html: opinionHtml(opinions[0])
                })
            })
            .catch(error => {
                console.log(error)
            })
    }

    $('.showOpinion').on('click', e => {
        showOpinions(e.target.getAttribute('data-id'))
    })
})
</script>