import React, { useState, useEffect } from 'react';

const EmailList = () => {
    const [emails, setEmails] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchEmails();
    }, []);

    const fetchEmails = async () => {
        try {
            const response = await fetch(`${window.ajaxurl}?action=clec_get_emails`, {
                headers: {
                    'X-WP-Nonce': window.clec_nonce
                }
            });
            const data = await response.json();
            setEmails(data);
            setLoading(false);
        } catch (error) {
            console.error('Error fetching emails:', error);
            setLoading(false);
        }
    };

    if (loading) return <div>Loading...</div>;

    return (
        <div className="wrap">
            <h1>Content Locker Email List</h1>
            <div className="clec-admin-content">
                <h2>How to Use</h2>
                <p>Use the shortcode [content_lock]Your content here[/content_lock] to lock any content.</p>
                
                <h2>Collected Emails</h2>
                <table className="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        {emails.map(email => (
                            <tr key={email.id}>
                                <td>{email.email}</td>
                                <td>{`${email.first_name || ''} ${email.last_name || ''}`}</td>
                                <td>{email.phone || '-'}</td>
                                <td>{email.created_at}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default EmailList;