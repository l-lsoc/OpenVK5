window.processedEvents = [1];

toastr.options = {
  "closeButton": true,
  "debug": false,
  "newestOnTop": false,
  "progressBar": false,
  "positionClass": "toast-bottom-left",
  "preventDuplicates": true,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": 0,
  "extendedTimeOut": 0,
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut",
  "tapToDismiss": true
}

const notify = (title, text) => {
    nS.play();
    toastr["info"](text, title);
};

const dispatchMessage = e => {
    let notification = true;
    let messenger    = $("iframe#ovk-messenger").get(0);
    if(typeof messenger !== "undefined") {
        let evt = new TextEncoder().encode(JSON.stringify(e)).buffer;
        messenger.contentWindow.postMessage(evt, "*", [evt]);
        
        if(messenger.contentWindow.msgPeer[0] === e.from) notification = false;
    }

    if(notification) notify("Новое сообщение", e.text);
}

const longpool = () => {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "?/events", true);
    xhr.onreadystatechange = () => {
        if (xhr.readyState != 4) return;
        
        let event;
        try {
            event = JSON.parse(xhr.responseText);
            if(window.processedEvents.indexOf(event.uuid) !== -1) return console.warn(`[Event] Duplicate event: №${event.uuid}`);
            window.processedEvents.push(event.uuid);
            
            if(event.title == undefined) throw new DOMError();
            notify(event.title, event.html);
        } catch(e) {
            try {
                if(event.isMessage) dispatchMessage(event);
            } catch(e) {
                if(typeof event !== "undefined") console.warn("[Event] Unknown event type!");
            }
        } finally {
            window.setTimeout(longpool, 1000);
        }
    };
    xhr.send();
} 

console.debug("[Event] Ready");
longpool();
