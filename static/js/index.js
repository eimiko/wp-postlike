 jQuery(document).on('click', '[data-action="postlike"]', function() {
    var $this = jQuery(this),
        wrap = jQuery("#post-action"),
        result = '';
    if ($this.hasClass('is-active')) {
        return false;

    } else {
        $this.addClass('is-active');
        var id = $this.data("id"),
            action = $this.data('action-value'),
            ajax_data = {
                action: "postlike",
                actionname: action,
                id: id
            };
        $.ajax({
            type: "POST",
            url: wpl.ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function(data) {
                if (data.status == 200) {
                    if (action == 'ding') {
                        result = '<span class="action-item is-active">顶(' + data.data.like + ')</span><span class="action-item">踩(' + data.data.dislike + ')</span>';
                    } else {
                        result = '<span class="action-item">顶(' + data.data.like + ')</span><span class="is-active action-item">踩(' + data.data.dislike + ')</span>';
                    }

                    wrap.html(result);
                } else {
                    console.log(data.data);
                }
            }
        });
    }
});
