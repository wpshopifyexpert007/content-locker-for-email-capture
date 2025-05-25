import React, { useState } from 'react';

const ContentLocker = ({ content, settings }) => {
    const [formData, setFormData] = useState({
        email: '',
        first_name: '',
        last_name: '',
        phone: ''
    });
    const [loading, setLoading] = useState(false);
    const [message, setMessage] = useState('');
    const [unlocked, setUnlocked] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const response = await fetch(`${window.ajaxurl}?action=clec_submit_email`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': window.clec_nonce
                },
                body: JSON.stringify(formData)
            });
            const data = await response.json();
            if (data.success) {
                setMessage('Success! Content unlocked.');
                setUnlocked(true);
            } else {
                setMessage(data.data || 'Error occurred');
            }
        } catch (error) {
            setMessage('Error submitting form');
        }
        setLoading(false);
    };

    if (unlocked) {
        return <div dangerouslySetInnerHTML={{ __html: content }} />;
    }

    return (
        <div className={`content-locker-wrapper content-locker-style-${settings.form_style}`}>
            <div className="email-capture-form">
                <h3>{settings.form_title}</h3>
                <p>{settings.form_description}</p>
                {message && <div className="clec-message">{message}</div>}
                <form onSubmit={handleSubmit}>
                    {settings.enable_first_name && (
                        <input
                            type="text"
                            placeholder="First Name"
                            value={formData.first_name}
                            onChange={(e) => setFormData({...formData, first_name: e.target.value})}
                            required
                        />
                    )}
                    {settings.enable_last_name && (
                        <input
                            type="text"
                            placeholder="Last Name"
                            value={formData.last_name}
                            onChange={(e) => setFormData({...formData, last_name: e.target.value})}
                            required
                        />
                    )}
                    <input
                        type="email"
                        placeholder="Email Address"
                        value={formData.email}
                        onChange={(e) => setFormData({...formData, email: e.target.value})}
                        required
                    />
                    {settings.enable_phone && (
                        <input
                            type="tel"
                            placeholder="Phone Number"
                            value={formData.phone}
                            onChange={(e) => setFormData({...formData, phone: e.target.value})}
                            required
                        />
                    )}
                    <button
                        type="submit"
                        disabled={loading}
                        style={{backgroundColor: settings.button_color}}
                    >
                        {loading ? 'Processing...' : settings.button_text}
                    </button>
                </form>
            </div>
        </div>
    );
};

export default ContentLocker;