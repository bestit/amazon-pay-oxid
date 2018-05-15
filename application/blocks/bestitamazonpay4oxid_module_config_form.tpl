[{$smarty.block.parent}]
[{capture name="sBestitAmazonModulConfigScript"}]
    var form = document.getElementsByName('module_configuration')[0];
    var oldState = new URLSearchParams(new FormData(form)).toString();

    form.addEventListener(
        'submit',
        function() {
            oldState = new URLSearchParams(new FormData(form)).toString();
        },
        false
    );

    window.onbeforeunload = function() {
        if (oldState !== new URLSearchParams(new FormData(form)).toString()) {
            return 'Amazon: Do you want to save your changes?';
        }

        return null;
    };
[{/capture}]
[{oxscript add=$smarty.capture.sBestitAmazonModulConfigScript}]