import app from 'flarum/app';
import SettingsModal from 'flarum/components/SettingsModal';

// just to make things easier
const settingsPrefix = 'alongwy-es.';
const translationPrefix = 'alongwy-es.admin.settings.';

export default class ESettingsModal extends SettingsModal {
    className() {
        return 'ESettingsModal Modal';
    }

    title() {
        return app.translator.trans(translationPrefix + 'title');
    }

    /**
     * Build modal form.
     *
     * @returns {*}
     */
    form() {
        return [
            m('h3', app.translator.trans(translationPrefix + 'ESOptionsHeading')),
            m('hr'),
            m('.Form-group', [
                m('label', app.translator.trans(translationPrefix + 'host')),
                m('.helpText', app.translator.trans(translationPrefix + 'hostHelp')),
                m('input[type=text].FormControl', {
                    bidi: this.setting(settingsPrefix + 'host'),
                    placeholder: 'localhost'
                })
            ]),
            m('.Form-group', [
                m('label', app.translator.trans(translationPrefix + 'port')),
                m('.helpText', app.translator.trans(translationPrefix + 'portHelp')),
                m('input[type=text].FormControl', {
                    bidi: this.setting(settingsPrefix + 'port'),
                    placeholder: "9200"
                })
            ]),
            m('.Form-group', [
                m('label', app.translator.trans(translationPrefix + 'scheme')),
                m('.helpText', app.translator.trans(translationPrefix + 'schemeHelp')),
                m('input[type=text].FormControl', {
                    bidi: this.setting(settingsPrefix + 'scheme'),
                    placeholder: "http"
                })
            ])
        ];
    }
}