import React, { useState, useEffect } from 'react';

const EmailMarketing = () => {
    const [settings, setSettings] = useState({
        enable_mailchimp: false,
        mailchimp_api_key: '',
        mailchimp_list_id: ''
    });
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');

    useEffect(() => {
        fetchSettings();
    }, []);

    const fetchSettings = async () => {
        try {
            const response = await fetch(`${window.ajaxurl}?action=clec_get_mailchimp_settings`, {
                headers: {
                    'X-WP-Nonce': window.clec_nonce
                }
            });
            const data = await response.json();
            setSettings(data);
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        try {
            const response = await fetch(`${window.ajaxurl}?action=clec_save_mailchimp_settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.clec_nonce
                },
                body: JSON.stringify(settings)
            });
            const data = await response.json();
            setMessage('Settings saved successfully!');
            setTimeout(() => setMessage(''), 3000);
        } catch (error) {
            console.error('Error saving settings:', error);
            setMessage('Error saving settings');
        }
        setSaving(false);
    };

    return (
        <div className="wrap">
            <h1>Email Marketing Settings</h1>
            {message && <div className="notice notice-success"><p>{message}</p></div>}
            <form onSubmit={handleSubmit}>
                <table className="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">Enable Mailchimp Integration</th>
                            <td>
                                <label>
                                    <input
                                        type="checkbox"
                                        checked={settings.enable_mailchimp}
                                        onChange={(e) => setSettings({
                                            ...settings,
                                            enable_mailchimp: e.target.checked
                                        })}
                                    />
                                    Enable Mailchimp
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Mailchimp API Key</th>
                            <td>
                                <input
                                    type="text"
                                    className="regular-text"
                                    value={settings.mailchimp_api_key}
                                    onChange={(e) => setSettings({
                                        ...settings,
                                        mailchimp_api_key: e.target.value
                                    })}
                                />
                                <p className="description">Enter your Mailchimp API key</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">Mailchimp List ID</th>
                            <td>
                                <input
                                    type="text"
                                    className="regular-text"
                                    value={settings.mailchimp_list_id}
                                    onChange={(e) => setSettings({
                                        ...settings,
                                        mailchimp_list_id: e.target.value
                                    })}
                                />
                                <p className="description">Enter your Mailchimp List/Audience ID</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <button type="submit" className="button button-primary" disabled={saving}>
                    {saving ? 'Saving...' : 'Save Changes'}
                </button>
            </form>
        </div>
    );
};

export default EmailMarketing;