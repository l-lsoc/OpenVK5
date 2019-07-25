window.OpenVK5 = {};

const sleep = t => new Promise(r => window.setTimeout(r, t));

OpenVK5.init = () => {
    $(document).ready(function(){
        $("a[rel^='post-attachment']").prettyPhoto();
    });
    $(".ovk-like, .ovk-repost").popover({
        delayIn: 1000,
        delayOut: 555,
        fallback: "Понравилось",
        placement: "below",
        html: true,
        offset: 10
    });
    $(".ovk-repost").click(function() {
        $("input[name=message]", $(this).parent()).val(prompt("Ваше дополнение: "));
    });
    $(".ovk-like, .ovk-delete, .ovk-repost").click(function() {
        $(this).parent().submit();
    });
    $(".ovk-post-edit").click(function() {
        let post    = $(this).parent().parent().parent();
        let content = $($(".post-content > p", post).get(0)).text();
        $(".post-content", post).remove();
        $(".post-menu", post).remove();
        $(".post-comments", post).remove();
        $("hr", post).remove();
        
        $(".post-edit-content", post).val(content);
        $(".post-edit-field", post).show();
    });
    $(".ovk-comment").click(function() {
        let post    = $(this).parent().parent();
        let form    = $(".new-comment", post);
        $(".new-comment").not(form).hide();
        form.toggle();
    });
    const attList  = $("#attachmentList > tbody");
    const attRes   = $("#attachments");
    $("#uploadImageAttachment").click(async () => {
        let attInput = $("#attachmentImage").get(0);
    
        file = attInput.files[0];
        if(typeof file == "undefined") file = window.fileSurrogate;
        if(typeof file == "undefined") return alert("Файл не выбран!");
        
        $("#uploadImageAttachment").button("loading");
        await sleep(500); //wait for button to change state
        let fd  = new FormData();
        let xhr = new XMLHttpRequest();
        fd.append("blob[]", file, ".upload");
        xhr.open("POST", "?/upload&type=image", false);
        xhr.send(fd);
        let result = "{'result': false}";
        try {
            result = JSON.parse(xhr.responseText);
        } catch(e) {
            $("#uploadImageAttachment").button("reset");
            return alert("Произошла ошибка.");
        }
        if(!result.result) return alert("Произошла ошибка.");
        window.fileSurrogate = undefined;
        
        attList.append(`<tr><td><img src="cdn/images/${ result.hash.substring(0, 2)+"/"+result.hash }/thumbnail.gif" /></td><td>${ result.id }</td></tr>`);
        
        $("#uploadImageAttachment").button("reset");
        let attachments = attRes.val().split(",");
        attachments.push(`Image::${ result.id }`);
        attRes.val(attachments.join(","));
        $(attInput).val("");
    });
    $("#uploadVideoAttachment").click(async () => {
        let attInput = $("#attachmentVideo").get(0);
    
        file = attInput.files[0];
        if(typeof file == "undefined") return alert("Файл не выбран!");
        
        $("#uploadVideoAttachment").button("loading");
        await sleep(500); //wait for button to change state
        let fd  = new FormData();
        let xhr = new XMLHttpRequest();
        fd.append("blob", file, ".upload");
        xhr.open("POST", "?/upload&type=video", false);
        xhr.send(fd);
        let result = "{'result': false}";
        try {
            result = JSON.parse(xhr.responseText);
        } catch(e) {
            $("#uploadVideoAttachment").button("reset");
            return alert("Произошла ошибка.");
        }
        if(!result.result) return alert("Произошла ошибка.");
        
        attList.append(`<tr><td><img src="cdn/videos/${ result.hash.substring(0, 2)+"/"+result.hash }.thumb.gif" style="max-width: 128px;" /></td><td>${ result.id }</td></tr>`);
        
        $("#uploadVideoAttachment").button("reset");
        let attachments = attRes.val().split(",");
        attachments.push(`Video::${ result.id }`);
        attRes.val(attachments.join(","));
        $(attInput).val("");
    });
    $(".wall-new-post > textarea").bind("paste", async event => {
        let items = (event.clipboardData || event.originalEvent.clipboardData).items;
        let item  = null;
        for(each in items) {
            if(items[each].kind === "file" && items[each].type.startsWith("image")) {
                item = items[each];
                break;
            }
        }
        if(item != null) {
            window.fileSurrogate = item.getAsFile();
            $("#attachment-button").click();
            await sleep(800);
            $("#uploadImageAttachment").click();
        }
    });
    $("li.disabled a").click(() => false);
} 

OpenVK5.destructor = () => $("body").unbind();

$(document).ready(OpenVK5.init);
