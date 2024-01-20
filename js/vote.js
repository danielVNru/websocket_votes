

$('.vote').each( function () {
    $( this ).click(()=>{
        $.ajax({
            url: '/server/vote',
            method: 'post',
            data: {
                vote: $( this ).attr('id').replace('vote', '')
            },
            success: data => {
                console.log(data);
                $('.vote').remove()
                $(".song").not(".song"+data.vote).append('<div class="-empty"></div>')
                $(".song"+data.vote).append('<div class="your-choise">Твой выбор!</div>')

                soket.send(JSON.stringify({t:'vote', token: token}))
            },
            error: ({ responseJSON }) => {
                console.log(responseJSON);
            }
        })
    })
} )