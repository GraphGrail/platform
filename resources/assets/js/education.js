window.showEducationBlock =

    function (url) {
        if (url) {
            $('#modalNextButton').attr.href.val(url)
        }

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

            if (imdb) {
                if ($(this).attr('id') === 'select_second') {
                    $('#dataset').val(imdb.val());
                    $('#name').val(imdb.html());
                } else {
                    $('#dataset').val(toxic.val());
                    $('#name').val(toxic.html());
                }
            }

            $('#educationModal').modal('hide');
        });
    };

$('#educationModal').modal({
    backdrop: 'static',
    keyboard: false
});
