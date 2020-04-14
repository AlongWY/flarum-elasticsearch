import app from 'flarum/app';
import ESettingsModal from "./modals/ESettingsModal";

// initialize settings modal
app.initializers.add('alongwy-elasticsearch', app => {
    app.extensionSettings['alongwy-elasticsearch'] = () => app.modal.show(new ESettingsModal());
});