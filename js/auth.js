

$('.go').click(()=>{
    $.ajax({
        url: '/server/auth',
        method: 'post',
        data: {
            login: $('.log').val(),
            pass: $('.pas').val()
        },
        success: data => {
            document.cookie = 'token='+data.token
            window.location.reload()
        },
        error: ({ responseJSON }) => {

            $('.log').addClass('--err-f')
            $('.log')[0].placeholder = responseJSON.log
            $('.pas').addClass('--err-f')
            $('.pas')[0].placeholder = responseJSON.pas
        }
    })
})