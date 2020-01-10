import app from 'flarum/app';
import ESettingsModal from "./models/ESettingsModel";

// initialize settings modal
app.initializers.add('alongwy-es', () => {
    app.extensionSettings['alongwy-es'] = () => app.modal.show(new ESettingsModal());
});