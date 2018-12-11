  jQuery(document).ready(function($) {

    /** File Upload **/
    $('.file-upload-btn').each(function() {
      var uploadBtn = $(this);
      uploadBtn.click(function(e) {
        e.preventDefault();
          var file = wp.media({ 
          title: $(this).attr('data-title'),
          // mutiple: true if you want to upload multiple files at once
          multiple: false
        }).open()
        .on('select', function(e) {
          // This will return the selected image from the Media Uploader, the result is an object
          var uploaded_file = file.state().get('selection').first();
          // We convert uploaded_image to a JSON object to make accessing it easier
          var file_url = uploaded_file.toJSON().url;
          // Let's assign the url value to the input field
          uploadBtn.prev('.file-upload-url').val(file_url);
        });
      });
    });


    /** Callback Page Setup **/
    var internalField = $('#callback_page_internal_url');
    var externalField = $('#callback_page_external_url');
    var urlType = 'input[name=callback_page_url_type]';

    function getCallbackUrl(url) {
      $('#callback_page').val(url);
    }

    function displayCallbackPageOptions() {
      var selectedUrlType = $(urlType + ':checked').val();

      internalField.addClass('hidden');
      externalField.addClass('hidden');

      if (selectedUrlType == 'internal') {
        internalField.removeClass('hidden');
        getCallbackUrl(internalField.find('option:selected').val());
        internalField.change(function() {
          getCallbackUrl($(this).val());
        });
      } else {
        externalField.removeClass('hidden');
        externalField.keyup(function() {
          getCallbackUrl($(this).val());
        });
      }
    }

    displayCallbackPageOptions();
    $(urlType).change(displayCallbackPageOptions);
  });