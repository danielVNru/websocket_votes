function anim() {
    let anim_items = document.querySelectorAll('.anim')
    let wind = $(window)
        let top = wind.scrollTop()
        let height = wind.height()

        Array.from(anim_items).forEach(item => {

            let block_top = $(item).offset().top
            if (top + height - 50 >= block_top) {
                // alert()
                item.classList.add(item.getAttribute('anim'))
                console.log(item, item.getAttribute('anim'));
            }

        })

    console.log('is anim!', anim_items);
}

anim()