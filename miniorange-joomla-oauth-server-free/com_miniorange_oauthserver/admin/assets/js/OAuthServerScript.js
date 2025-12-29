function add_css_tab(element) 
{
    
    jQuery(".mo_nav_tab_active").removeClass("mo_nav_tab_active");
    jQuery(element).addClass("mo_nav_tab_active");

} 
function cancel_form() 
{
    jQuery('#oauth_cancel_form').submit();
}

function back_btn(){

    jQuery('#mo_otp_cancel_form').submit();
}

function resend_otp(){
    jQuery('#resend_otp_form').submit();
}

function oauth_account_exist(){
    jQuery('#resend_otp_form').submit();
}

function copyToClipboard(element) 
{
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    temp.val(jQuery(element).text()).select();
    document.execCommand("copy");
    temp.remove();  
}

function validateEmail(emailField) {
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

    if (reg.test(emailField.value) == false) {
        document.getElementById("email_error").style.display = "block";
        document.getElementById("submit_button").disabled = true;
    } else {
        document.getElementById("email_error").style.display = "none";
        document.getElementById("submit_button").disabled = false;
    }
    
}
function cancel_update(){
    jQuery("#cancelUpdate").submit();
}

function displayFileName() {
    var fileInput = document.getElementById('fileInput');
    var file = fileInput.files[0];

    // Check if a file is selected and if it is a JSON file
    if (file && file.name.endsWith('.json')) {
        document.getElementById('fileName').textContent = file.name;
    } else {
        document.getElementById('fileName').textContent = "Please select a .json file.";
    }
}

function toggleCollapse(contentId, iconElement) {
    let content = document.getElementById(contentId);
    if (content.style.display === "none" || content.style.display === "") {
        content.style.display = "block";
        iconElement.textContent = "-";
    } else {
        content.style.display = "none";
        iconElement.textContent = "+";
    }
}

function changeSubMenu(tabPanelId, element0, element1) {
    var $panel = jQuery(tabPanelId);
    $panel.find('.mo_oauth_sub_menu_active').removeClass('mo_oauth_sub_menu_active');
    jQuery(element0).addClass('mo_oauth_sub_menu_active');
    jQuery(element1).nextAll('div').css('display', 'none');
    jQuery(element1).prevAll().css('display', 'none');
    jQuery(element1).css('display', 'block');
}

function toggleFeatureList(headerId) {
    const list = document.getElementById(headerId);
    const arrow = list.previousElementSibling.querySelector(".mo_oauth_feature_arrow i");

    if (list.style.display === "none") {
        list.style.display = "block";
        arrow.classList.remove("fa-chevron-down");
        arrow.classList.add("fa-chevron-up");
    } else {
        list.style.display = "none";
        arrow.classList.remove("fa-chevron-up");
        arrow.classList.add("fa-chevron-down");
    }
}