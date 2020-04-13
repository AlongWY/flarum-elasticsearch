import app from 'flarum/app';
import ESettingsModal from "./modals/ESettingsModal";

// initialize settings modal
app.initializers.add('alongwy-es', app => {
    app.extensionSettings['alongwy-es'] = () => app.modal.show(new ESettingsModal());
});