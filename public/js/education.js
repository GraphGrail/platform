window.showEducationBlock=function(t){t&&$("#modalNextButton").attr.href.val(t),$(".educationBlock-button").click(function(){var t=null,a=null;$("#dataset option").each(function(){-1!==$(this).html().toLowerCase().indexOf("imdb")&&(t=$(this)),-1!==$(this).html().toLowerCase().indexOf("toxic")&&(a=$(this))}),"select_second"===$(this).attr("id")?($("#dataset").val(t.val()),$("#name").val(t.html())):($("#dataset").val(a.val()),$("#name").val(a.html())),$("#educationModal").modal("hide")})},$("#educationModal").modal({backdrop:"static",keyboard:!1});