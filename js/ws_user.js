const soket = new WebSocket("ws://dj:8090/ws/index.php")

soket.addEventListener('message', ({data})=>{
    data = JSON.parse(data)
    console.log(data);
    if(data.type == 'next'){


        if(data.is_end){
            console.log('is_end');
            $('.voting').html('')
            $('.voting').load('./end.html', ()=>{
                anim()
            })
            return
        }

        $('.voting').html('')

        for (let i = 1; i <= 3; i++){
            
            let an = ''

            if(i == 1) an = 'left_a'
                else if (i == 2) an = 'def_a'
                else an = 'right_a'

            $('.voting').append('<div anim="' + an + '" class="voting__optoin anim song song'+i+'"><div class="song__img img'+i+'"></div><h2 class="song__name">'+ data['track'+i].name +'</h2><p class="song__author">'+ data['track'+i].artist +'</p><input type="button" id="vote'+i+'" value="Проголосовать!" class="btn vote"></div>')

            $('.img'+i).css({backgroundImage: 'url('+data['track'+i].img+')'})
        }

        anim()


        $('.vote').each( function () {
            $( this ).click(()=>{
                $.ajax({
                    url: '/server/vote',
                    method: 'post',
                    data: {
                        vote: $( this ).attr('id').replace('vote', '')
                    },
                    success: data => {
                        $('.vote').remove()
                        $(".song").not(".song"+data.vote).append('<div class="-empty"></div>')
                        $(".song"+data.vote).append('<div class="your-choise">Твой выбор!</div>')
                        soket.send(JSON.stringify({t:'vote', token: token}))
                    },
                    error: ({ responseJSON }) => {
                    }
                })
            })
        } )
    }
})

soket.addEventListener('open', ()=>{
    console.log('Соединение установленно!');
})