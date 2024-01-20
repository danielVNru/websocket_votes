let is_sending = true

const soket = new WebSocket("ws://dj:8090/ws/index.php")

$('.stop').click(()=>{

    if(is_sending){
        $.ajax({
            url: '/server/next',
            method: 'post',
            success: data => {
                let mess = {
                    token: token,
                    t: 'next'
                }
            
                soket.send(JSON.stringify(mess))
            },
        })
    }

    is_sending = false
    
})

soket.addEventListener('message', ({data})=>{
    data = JSON.parse(data)
    console.log('Сообщение!');
    if(data.type == 'next'){
        location.reload()
    } else if(data.type == 'vote'){
        $('.song__score1,.song__score2,.song__score3').removeClass('--win')
        if(data.votes1 >= data.votes2 && data.votes1 >= data.votes3) $('.song__score1').addClass('--win')
        if(data.votes2 >= data.votes1 && data.votes2 >= data.votes3) $('.song__score2').addClass('--win')
        if(data.votes3 >= data.votes2 && data.votes3 >= data.votes1) $('.song__score3').addClass('--win')
        for (let i = 1; i <= 3; i++){
            
            $('.song__score'+i).html(data['votes'+i]+'%')
            
        }
    }
})
soket.addEventListener('open', ()=>{
    console.log('Соединение установленно!');
})