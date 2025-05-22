(function ($) {
  "use strict";

  $(document).ready(function () {
    // FAQ Accordion functionality
    $(".ssss-faq-question").on("click", function () {
      var $item = $(this).closest(".ssss-faq-item");
      var $toggle = $(this).find(".ssss-faq-toggle");

      // Close all other FAQ items
      $(".ssss-faq-item").not($item).removeClass("active");

      // Toggle current item
      $item.toggleClass("active");
    });

    // Shortcode copy to clipboard functionality
    $(".ssss-shortcode-copy").on("click", function () {
      var shortcode = $(this).data("shortcode");

      // Create temporary textarea to copy text
      var tempTextarea = $("<textarea>");
      $("body").append(tempTextarea);
      tempTextarea.val(shortcode).select();

      try {
        // Copy to clipboard
        document.execCommand("copy");

        // Show notification
        var $notification = $("#ssss-copy-notification");
        $notification.addClass("show");

        // Hide notification after 2 seconds
        setTimeout(function () {
          $notification.removeClass("show");
        }, 2000);
      } catch (err) {
        console.error("Failed to copy shortcode:", err);
      }

      // Remove temporary textarea
      tempTextarea.remove();
    });

    // Modern clipboard API fallback for newer browsers
    if (navigator.clipboard) {
      $(".ssss-shortcode-copy")
        .off("click")
        .on("click", function () {
          var shortcode = $(this).data("shortcode");

          navigator.clipboard
            .writeText(shortcode)
            .then(function () {
              // Show notification
              var $notification = $("#ssss-copy-notification");
              $notification.addClass("show");

              // Hide notification after 2 seconds
              setTimeout(function () {
                $notification.removeClass("show");
              }, 2000);
            })
            .catch(function (err) {
              console.error("Failed to copy shortcode:", err);
            });
        });
    }
  });
})(jQuery);
