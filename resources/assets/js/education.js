window.showEducationBlock =

    function (text, url) {
        let button = '';

        if (url) {
            button =  '<a class="educationBlock-button" href="' + url + '">Next</a>'
        } else {
            button =  '<a id="select_second" class="educationBlock-button">Toxic comments dataset</a><a class="educationBlock-button" id="select_first">IMDB dataset</a>'
        }

        $('body').append('<div class="educationBlock"><p>' + text + '</p>' + button + '</div>');

        $('.educationBlock-button').click(function() {
            let imdb = null;
            let toxic = null;

            $('#dataset option').each(function () {
                if (-1 !== $(this).html().toLowerCase().indexOf('imdb')) {
                    imdb = $(this);
                }
                if (-1 !== $(this).html().toLowerCase().indexOf('toxic')) {
                    toxic = $(this);
                }
            });
            if ($(this).attr('id') === 'select_first') {
                $('#dataset').val(imdb.val());
                $('#name').val(imdb.html());
            } else {
                $('#dataset').val(toxic.val());
                $('#name').val(toxic.html());
            }
            $('.educationBlock').hide();
        });
    };
