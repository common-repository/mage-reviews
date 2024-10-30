jQuery('span.rating-options .btn').click(function(){
  jQuery(this).find('input:radio').attr('checked', true);
});