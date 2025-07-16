jQuery(document).ready(function($) {
    var current = 0;
    var fieldsets = $("#cra-multistep-form fieldset:not(#cra-success)");
    var progressbar = $("#cra-progressbar li");

    function showStep(i) {
        fieldsets.hide().eq(i).show();
        progressbar.removeClass("active").slice(0, i + 1).addClass("active");
    }

    showStep(current);

    $(".next").on("click", function(e) {
        e.preventDefault();

        if ($(this).attr("id") === "cra-company-next") {
            let companyNumber = $("#cra_company_number").val();
            if (!companyNumber) {
                alert("Company Number is required.");
                return;
            }

            $.post(
                cra_ajax_object.ajaxurl, {
                    action: 'validate_company_number',
                    security: cra_ajax_object.security,
                    company_number: companyNumber
                },
                function(response) {
                    if (response.success) {
                        $("#company-valid-message").text(response.data).show();
                        current++;
                        showStep(current);
                        craFillReview();
                    } else {
                        alert(response.data);
                    }
                }
            );
        } else {
            current++;
            showStep(current);
        }
    });

    $(".previous").on("click", function(e) {
        e.preventDefault();
        current--;
        showStep(current);
    });

    function craFillReview() {
        let out = '<ul>';
        $("#cra-multistep-form input[type=text], input[type=email]").each(function() {
            let label = $(this).attr("placeholder");
            out += `<li><strong>${label}:</strong> ${$(this).val()}</li>`;
        });
        out += '</ul>';
        $("#cra-review-summary").html(out);
    }

    $("#cra-multistep-form").on("submit", function(event) {
        $("#cra-success").show();
        fieldsets.hide();
    });
});
