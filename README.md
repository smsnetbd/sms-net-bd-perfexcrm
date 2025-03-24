# SMS.net.bd Module for Perfex CRM

![sms.net.bd + Perfex CRM](./assets/img/sms-net-bd-logo.png)

Easily integrate **[sms.net.bd](https://sms.net.bd)** with **Perfex CRM** to send SMS notifications, check balance, retrieve delivery reports, and manage SMS seamlessly.

## Features
- Send SMS directly from Perfex CRM  
- Check SMS balance  
- Retrieve delivery reports  
- Secure API-based communication  
- Easy configuration with API Key  
- Enable or disable Test Mode  
- Log sent messages for tracking  
- **Automated Triggers:**
  - **Invoice Overdue Notice**: Triggered when an overdue notice is sent to customer contacts.
  - **Invoice Due Notice**: Triggered when an invoice due notice is sent.
  - **Invoice Payment Recorded**: Triggered when an invoice payment is recorded.
  - **Estimate Expiration Reminder**: Triggered when an estimate expiration reminder is sent.
  - **Proposal Expiration Reminder**: Triggered when a proposal expiration reminder is sent.
  - **New Comment on Proposal (to Customer)**: Triggered when a staff member comments on a proposal (sent to the customer/lead).
  - **New Comment on Proposal (to Staff)**: Triggered when a customer/lead comments on a proposal (sent to the creator and assigned staff).
  - **New Comment on Contract (to Customer)**: Triggered when a staff member adds a comment to a contract (sent to customer contacts).
  - **New Comment on Contract (to Staff)**: Triggered when a customer adds a comment to a contract (sent to the contract creator).
  - **Contract Expiration Reminder**: Triggered when a contract expiration reminder is sent via Cron Job.
  - **Contract Sign Reminder**: Triggered when a contract is first sent and stops automatically once signed.
  - **Staff Reminder**: Triggered when staff is notified for a specific custom reminder.

## Prerequisites
- **Perfex CRM** version **3.2.1** or higher
- **SMS.net.bd** module version **1.0.1**
- An account on **[sms.net.bd](https://sms.net.bd/signup)**

## Installation Instructions

### 1. Download the Latest Release
Download the `smsnetbd.zip` file from the official **sms.net.bd** module repository.

### 2. Upload the Module
1. Log in to your **Perfex CRM Admin Panel**.
2. Navigate to **Setup** > **Modules**.
3. Click on **Upload Module**.
4. Upload the `smsnetbd.zip` file.

### 3. Activate the Module
1. Locate the module in the list of available modules.
2. Click **Activate**.

![sms.net.bd + Perfex CRM](./assets/img/screenshot1.png) 
![sms.net.bd + Perfex CRM](./assets/img/screenshot2.png)

### 4. Configure the Module
1. Navigate to **Setup** > **Settings** > **SMS** > **SMSAPI**.
2. Enter your **API Key**.
3. Configure other settings as required.

![sms.net.bd + Perfex CRM](./assets/img/screenshot3.png)

### 5. Start Using the Module
You can now send SMS notifications from Perfex CRM.

## Additional Configuration

### 1. API Key Setup
1. Log in to **sms.net.bd**.
2. Navigate to **API Menu** > **+ Generate API Key**.
3. Copy the generated token and paste it into the **API Key** field in the module settings.

### 2. Sender ID Configuration (Optional)
1. Log in to **sms.net.bd**.
2. Navigate to **Messaging** > **Create Sender ID**.
3. Add or select an approved **Sender ID**.
4. Choose **one** Sender ID in the module settings.

### 3. Test Mode Selection
- Allows testing SMS functionality before going live.
- Enable or disable **Test Mode** (`Yes` / `No`).
- **In test mode:** SMS will be logged but **not sent**.
- **Before going live, set Test Mode to `No` to enable real SMS sending.**

![sms.net.bd + Perfex CRM](./assets/img/screenshot4.png)

### 4. Log Sent Messages
- Logs all outgoing SMS messages.
- Enable or disable logging (`Yes` / `No`) in the settings.
- When enabled, all sent messages will be recorded.

![sms.net.bd + Perfex CRM](./assets/img/screenshot5.png)
![sms.net.bd + Perfex CRM](./assets/img/screenshot6.png)

## Troubleshooting
If you encounter issues, check the following:

- Ensure you have the correct **permissions** to upload and activate modules.
- Verify your **API Key** is correct.
- Check if **Test Mode** is disabled when sending real SMS.
- Ensure **sms.net.bd** services are operational.

For further assistance, contact **[sms.net.bd Support](https://sms.net.bd/contact)**.
