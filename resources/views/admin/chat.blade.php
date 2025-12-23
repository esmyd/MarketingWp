@extends('admin.layouts.app')

@section('header')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<style>
    * {
        box-sizing: border-box;
    }

    body {
        background: #111b21 !important;
        font-family: 'Segoe UI', 'Helvetica Neue', Helvetica, 'Lucida Grande', Arial, Ubuntu, Cantarell, 'Fira Sans', sans-serif !important;
        margin: 0;
        padding: 0;
        color: #e9edef;
    }

    /* Ocultar header blanco del layout */
    .content-header {
        display: none !important;
    }

    .main-content {
        padding: 0 !important;
    }

    /* Fondo con patrón sutil oscuro */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='grid' width='100' height='100' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 100 0 L 0 0 0 100' fill='none' stroke='%2322292e' stroke-width='0.5' opacity='0.3'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23grid)'/%3E%3C/svg%3E");
        opacity: 0.4;
        z-index: 0;
        pointer-events: none;
    }

    .wa-bubble-in {
        background: #202c33;
        color: #e9edef;
        border-radius: 7.5px 7.5px 7.5px 0;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.3);
        position: relative;
        padding: 6px 7px 8px 9px;
        max-width: 65%;
        word-wrap: break-word;
    }

    .wa-bubble-in::before {
        content: '';
        position: absolute;
        left: -8px;
        bottom: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 8px 13px 0;
        border-color: transparent #202c33 transparent transparent;
    }

    .wa-bubble-out {
        background: #005c4b;
        color: #e9edef;
        border-radius: 7.5px 7.5px 0 7.5px;
        box-shadow: 0 1px 0.5px rgba(0,0,0,0.3);
        position: relative;
        padding: 6px 7px 8px 9px;
        max-width: 65%;
        word-wrap: break-word;
        margin-left: auto;
    }

    .wa-bubble-out::after {
        content: '';
        position: absolute;
        right: -8px;
        bottom: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 0 13px 8px;
        border-color: transparent transparent #005c4b transparent;
    }

    .wa-btn-reply {
        display: inline-block;
        background: #25d366;
        color: #fff;
        font-weight: 500;
        border-radius: 18px;
        padding: 8px 12px;
        margin: 4px 0;
        font-size: 14px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        border: none;
        cursor: pointer;
        white-space: pre-line;
    }

    .wa-badge {
        display: none; /* Ocultar badges para estilo más limpio */
    }

    .wa-bubble-content {
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: pre-wrap;
        font-size: 14.2px;
        line-height: 19px;
        color: #e9edef;
    }

    .wa-main-bg {
        min-height: 100vh;
        background: #0b141a;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 0;
        position: relative;
        z-index: 1;
    }

    .wa-card {
        width: 100%;
        max-width: 1600px;
        height: 100vh;
        background: #111b21;
        border-radius: 0;
        box-shadow: none;
        display: flex;
        overflow: hidden;
        border: none;
    }

    .wa-sidebar {
        background: #202c33;
        border-right: 1px solid #313d45;
        min-width: 380px;
        max-width: 380px;
        width: 380px;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        z-index: 10;
        transition: transform 0.3s ease;
    }

    /* Mobile sidebar overlay */
    .wa-sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .wa-sidebar-overlay.show {
        display: block;
        opacity: 1;
    }

    /* Mobile toggle button */
    .wa-mobile-toggle {
        background: transparent;
        border: none;
        color: #e9edef;
        font-size: 1.25rem;
        padding: 0.5rem;
        cursor: pointer;
        border-radius: 4px;
        transition: background 0.2s ease;
        min-width: 40px;
        min-height: 40px;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .wa-mobile-toggle:hover {
        background: #2a3942;
    }

    .wa-mobile-toggle i {
        font-size: 1.1rem;
    }

    .wa-sidebar-header {
        background: #202c33;
        padding: 10px 16px;
        border-bottom: 1px solid #313d45;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 59px;
    }

    .wa-sidebar-header .wa-mobile-toggle {
        display: none;
    }

    .wa-sidebar-header-title {
        font-size: 18px;
        font-weight: 400;
        color: #e9edef;
        flex: 1;
    }

    .wa-sidebar-contacts {
        flex: 1;
        overflow-y: auto;
        background: #111b21;
    }

    .wa-sidebar-contacts::-webkit-scrollbar {
        width: 6px;
    }

    .wa-sidebar-contacts::-webkit-scrollbar-track {
        background: transparent;
    }

    .wa-sidebar-contacts::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
    }

    .wa-sidebar-contacts::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.2);
    }

    .wa-sidebar-contact {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-bottom: 1px solid #313d45;
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
        position: relative;
    }

    .wa-sidebar-contact:hover {
        background: #2a3942;
    }

    .wa-sidebar-contact.active {
        background: #2a3942;
    }

    .wa-sidebar-contact.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: #25d366;
    }

    .wa-sidebar-avatar {
        width: 49px;
        height: 49px;
        border-radius: 50%;
        background: #6a7175;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        color: #e9edef;
        font-size: 20px;
        flex-shrink: 0;
        position: relative;
    }

    .wa-sidebar-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .wa-sidebar-contact-info {
        flex: 1;
        min-width: 0;
    }

    .wa-sidebar-name {
        font-weight: 400;
        font-size: 17px;
        color: #e9edef;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wa-sidebar-phone {
        font-size: 14px;
        color: #8696a0;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .wa-sidebar-last-message {
        font-size: 14px;
        color: #8696a0;
        margin-top: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .wa-sidebar-time {
        font-size: 12px;
        color: #667781;
        white-space: nowrap;
        margin-left: auto;
        padding-left: 8px;
    }

    .wa-message-count-badge {
        background: #34B7F1;
        color: white;
        border-radius: 10px;
        padding: 0 6px;
        font-size: 11px;
        font-weight: 500;
        margin-left: 4px;
    }

    .wa-new-message-indicator {
        color: #25d366;
        font-size: 12px;
        margin-left: 4px;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }
    /* Chat Panel Styles */
    .wa-chat-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #0b141a;
        position: relative;
    }

    .wa-chat-panel::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='grid' width='100' height='100' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 100 0 L 0 0 0 100' fill='none' stroke='%2322292e' stroke-width='0.5' opacity='0.3'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23grid)'/%3E%3C/svg%3E");
        opacity: 0.4;
        pointer-events: none;
    }

    .wa-chat-header {
        background: #202c33;
        padding: 10px 16px;
        border-bottom: 1px solid #313d45;
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 1001;
        position: relative;
        min-height: 59px;
    }

    .wa-chat-header .wa-mobile-toggle {
        display: none;
        z-index: 1002;
        position: relative;
    }

    .wa-chat-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #6a7175;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 500;
        color: #e9edef;
        font-size: 18px;
        flex-shrink: 0;
        cursor: default;
        transition: opacity 0.2s ease;
    }

    @media (max-width: 991.98px) {
        .wa-chat-avatar {
            cursor: pointer;
        }

        .wa-chat-avatar:active {
            opacity: 0.7;
        }
    }

    .wa-chat-header-info {
        flex: 1;
        min-width: 0;
    }

    .wa-chat-header-name {
        font-size: 16px;
        font-weight: 400;
        color: #e9edef;
        line-height: 21px;
    }

    .wa-chat-header-status {
        font-size: 13px;
        color: #8696a0;
        line-height: 20px;
    }

    .wa-chat-bot-control {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    .bot-toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
    }

    .bot-toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .bot-toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #667781;
        transition: .4s;
        border-radius: 24px;
    }

    .bot-toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    .bot-toggle-switch input:checked + .bot-toggle-slider {
        background-color: #25d366;
    }

    .bot-toggle-switch input:checked + .bot-toggle-slider:before {
        transform: translateX(20px);
    }

    .bot-toggle-switch input:focus + .bot-toggle-slider {
        box-shadow: 0 0 1px #25d366;
    }

    .bot-toggle-switch:hover .bot-toggle-slider {
        opacity: 0.8;
    }

    .wa-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 2px;
        position: relative;
        z-index: 1;
        min-height: 0;
    }

    .wa-chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .wa-chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    .wa-chat-messages::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
    }

    .wa-chat-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(255,255,255,0.2);
    }

    .wa-message-wrapper {
        display: flex;
        margin-bottom: 2px;
        padding: 0 7.5px;
    }

    .wa-message-wrapper.incoming {
        justify-content: flex-start;
    }

    .wa-message-wrapper.outgoing {
        justify-content: flex-end;
    }

    .wa-message-time {
        font-size: 11px;
        color: #8696a0;
        margin-top: 2px;
        padding: 0 7px;
        display: flex;
        align-items: center;
        gap: 3px;
    }

    .wa-message-time.outgoing {
        justify-content: flex-end;
    }

    .wa-message-time.incoming {
        justify-content: flex-start;
    }

    .wa-message-status {
        display: inline-flex;
        align-items: center;
        margin-left: 3px;
    }

    .wa-input-container {
        background: #202c33;
        padding: 8px 16px;
        border-top: 1px solid #313d45;
        z-index: 100;
        position: relative;
    }

    .wa-chat-input-area {
        position: relative;
        z-index: 100;
    }

    .wa-input-wrapper {
        background: #2a3942;
        border-radius: 24px;
        display: flex;
        align-items: center;
        padding: 2px 2px 2px 12px;
        min-height: 52px;
    }

    .wa-input-button {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #8696a0;
        transition: background 0.2s;
        flex-shrink: 0;
    }

    .wa-input-button:hover {
        background: #313d45;
    }

    .wa-input-textarea {
        flex: 1;
        border: none;
        outline: none;
        padding: 9px 12px;
        font-size: 15px;
        line-height: 20px;
        color: #e9edef;
        resize: none;
        max-height: 100px;
        font-family: inherit;
        background: transparent;
    }

    .wa-input-textarea::placeholder {
        color: #8696a0;
    }

    .wa-send-button {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #25d366;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: #ffffff;
        transition: background 0.2s;
        flex-shrink: 0;
        border: none;
    }

    .wa-send-button:hover {
        background: #20ba5a;
    }

    .wa-send-button:disabled {
        background: #a0a0a0;
        cursor: not-allowed;
    }

    /* Preview containers */
    .wa-preview-container {
        margin-bottom: 8px;
        padding: 0 16px;
    }

    .wa-preview-box {
        background: #2a3942;
        border-radius: 8px;
        padding: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    .wa-preview-remove {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #f44336;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: white;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .wa-preview-remove:hover {
        background: #d32f2f;
    }

    .wa-recording-box {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 8px;
    }

    .wa-recording-dot {
        width: 12px;
        height: 12px;
        background: #f44336;
        border-radius: 50%;
        animation: pulse 1.5s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    /* New styles for stats panel */
    .stats-panel {
        background: transparent;
        border-radius: 0;
        padding: 10px 15px;
        margin-bottom: 10px;
        box-shadow: none;
        position: relative;
        z-index: 1;
    }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1em;
        margin-bottom: 1em;
    }
    .stat-card {
        background: #202c33;
        padding: 1em;
        border-radius: 0.8em;
        border: 1px solid #313d45;
    }
    .stat-title {
        font-size: 0.9em;
        color: #8696a0;
        margin-bottom: 0.5em;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .stat-info-icon {
        cursor: help;
        color: #8696a0;
        font-size: 0.85em;
        opacity: 0.7;
        transition: opacity 0.2s;
        position: relative;
    }
    .stat-info-icon:hover {
        opacity: 1;
        color: #25d366;
    }
    .stat-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: #202c33;
        color: #e9edef;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 0.8em;
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        border: 1px solid #313d45;
        margin-bottom: 5px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s;
        max-width: 250px;
        white-space: normal;
    }
    .stat-info-icon:hover .stat-tooltip {
        opacity: 1;
    }
    .stat-value {
        font-size: 1.8em;
        font-weight: 600;
        color: #e9edef;
    }
    .stat-change {
        font-size: 0.85em;
        margin-top: 0.3em;
    }
    .stat-change.positive {
        color: #25d366;
    }
    .stat-change.negative {
        color: #f15c6d;
    }
    .charts-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1em;
    }
    .chart-card {
        background: #202c33;
        padding: 1em;
        border-radius: 0.8em;
        border: 1px solid #313d45;
    }
    .topics-list {
        margin-top: 1em;
    }
    .topic-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5em 0;
        border-bottom: 1px solid #313d45;
    }
    .topic-name {
        font-size: 0.9em;
        color: #e9edef;
    }
    .topic-count {
        font-size: 0.85em;
        color: #e9edef;
        background: #2a3942;
        padding: 0.2em 0.6em;
        border-radius: 9999px;
    }
    /* Estilos para el selector de emojis */
    #emoji-picker {
        display: none !important;
    }
    #emoji-picker:not(.hidden) {
        display: block !important;
    }
    .emoji-btn {
        transition: background-color 0.15s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        min-height: 32px;
        background: transparent;
    }
    .emoji-btn:hover {
        background-color: #313d45 !important;
    }
    .emoji-btn:active {
        transform: scale(0.95);
    }

    /* Responsive Styles */
    @media (max-width: 991.98px) {
        .wa-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            z-index: 2000;
            transform: translateX(-100%);
            box-shadow: 2px 0 8px rgba(0,0,0,0.3);
        }

        .wa-sidebar.show {
            transform: translateX(0);
        }

        .wa-chat-panel {
            width: 100%;
        }

        .wa-sidebar-header .wa-mobile-toggle,
        .wa-chat-header .wa-mobile-toggle {
            display: flex !important;
        }

        .wa-card {
            flex-direction: column;
        }
    }

    @media (max-width: 767.98px) {
        .wa-sidebar {
            min-width: 100%;
            max-width: 100%;
            width: 100%;
        }

        .wa-chat-header {
            padding: 8px 12px;
        }

        .wa-chat-input-area {
            padding: 8px 12px;
        }

        .wa-bubble-in,
        .wa-bubble-out {
            max-width: 85%;
        }

        /* Ocultar o ajustar stats-panel en móvil para que no tape el input */
        .stats-panel {
            position: relative;
            z-index: 1;
            margin-bottom: 0;
            padding: 8px 10px;
            max-height: 200px;
            overflow-y: auto;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5em;
            margin-bottom: 0.5em;
        }

        .stat-card {
            padding: 0.6em;
            font-size: 0.85em;
        }

        .wa-input-container {
            position: sticky;
            bottom: 0;
            z-index: 1000;
            background: #202c33;
        }

        .wa-chat-input-area {
            position: relative;
            z-index: 1000;
        }

        /* Asegurar que el input siempre esté visible */
        .wa-chat-panel {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .wa-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding-bottom: 10px;
        }
    }
    </style>

<div class="wa-main-bg">
    <!-- Sidebar Overlay (Mobile) -->
    <div class="wa-sidebar-overlay" id="waSidebarOverlay"></div>

    <div class="wa-card">
        <!-- Sidebar -->
        <div class="wa-sidebar" id="waSidebar">
            <div class="wa-sidebar-header">
                <button class="wa-mobile-toggle" id="waCloseSidebar" title="Cerrar">
                    <i class="fas fa-times"></i>
                </button>
                <div class="wa-sidebar-header-title">Chats</div>
            </div>
            <div class="wa-sidebar-contacts">
            @foreach($contacts as $c)
                <a href="javascript:void(0)" data-contact-id="{{ $c->id }}" class="wa-sidebar-contact{{ $contact->id === $c->id ? ' active' : '' }}">
                    <div class="wa-sidebar-avatar">{{ strtoupper(mb_substr($c->name ?? 'C', 0, 1)) }}</div>
                        <div class="wa-sidebar-contact-info">
                        <div class="wa-sidebar-name">{{ $c->name ?? 'Cliente' }}</div>
                            <div class="wa-sidebar-phone">
                            <span>{{ $c->phone_number }}</span>
                            @if(isset($c->messages_count) && $c->messages_count > 0)
                                    <span class="wa-message-count-badge" data-contact-id="{{ $c->id }}">{{ $c->messages_count }}</span>
                            @endif
                            @if(isset($c->has_new_message) && $c->has_new_message)
                                    <span class="wa-new-message-indicator" data-contact-id="{{ $c->id }}">●</span>
                            @endif
                        </div>
                        @if(!empty($c->last_client_message))
                                <div class="wa-sidebar-last-message">
                                @php
                                    $lastMessage = $c->last_client_message;
                                    try {
                                        $decoded = json_decode($lastMessage, true);
                                        if (is_array($decoded)) {
                                            if (isset($decoded['type']) && $decoded['type'] === 'button_reply' && isset($decoded['button_reply']['title'])) {
                                                echo $decoded['button_reply']['title'];
                                            } elseif (isset($decoded['type']) && $decoded['type'] === 'list_reply' && isset($decoded['list_reply']['title'])) {
                                                echo $decoded['list_reply']['title'];
                                            } elseif (isset($decoded['title'])) {
                                                echo $decoded['title'];
                                            } else {
                                                echo strip_tags($lastMessage);
                                            }
                                        } else {
                                            echo strip_tags($lastMessage);
                                        }
                                    } catch (\Throwable $e) {
                                        echo strip_tags($lastMessage);
                                    }
                                @endphp
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
            </div>
        </div>
        <!-- Chat Panel -->
        <div class="wa-chat-panel">
            <!-- Header -->
            <div class="wa-chat-header">
                <button class="wa-mobile-toggle" id="waOpenSidebar" title="Abrir lista de chats" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="wa-chat-avatar" id="waChatAvatarToggle" title="Ver contactos">{{ strtoupper(mb_substr($contact->name ?? 'C', 0, 1)) }}</div>
                <div class="wa-chat-header-info">
                    <div class="wa-chat-header-name">{{ $contact->name ?? 'Cliente' }}</div>
                    <div class="wa-chat-header-status">{{ $contact->phone_number }}</div>
                </div>
                <!-- Control del Bot -->
                <div class="wa-chat-bot-control">
                    <span style="font-size: 12px; color: #8696a0;">Bot:</span>
                    <label class="bot-toggle-switch">
                        <input type="checkbox" id="bot-enabled-toggle"
                               {{ ($contact->bot_enabled ?? true) ? 'checked' : '' }}
                               data-contact-id="{{ $contact->id }}">
                        <span class="bot-toggle-slider"></span>
                    </label>
                    <span id="bot-status-text" style="font-size: 12px; color: #8696a0; min-width: 60px;">
                        {{ ($contact->bot_enabled ?? true) ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>
            </div>
            <!-- Mensajes -->
            <div class="wa-chat-messages" id="chat-messages">
                @forelse($messages as $msg)
                    @php
                        $isIncoming = $msg->sender_type === 'client';
                        $bubbleClass = $isIncoming ? 'wa-bubble-in' : 'wa-bubble-out';
                        $align = $isIncoming ? 'justify-start' : 'justify-end';
                        $content = $msg->content;
                        $decoded = null;
                        try {
                            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                        } catch (\Throwable $e) {
                            $decoded = null;
                        }
                    @endphp
                    <div class="wa-message-wrapper {{ $isIncoming ? 'incoming' : 'outgoing' }}" data-message-id="{{ $msg->id }}">
                        <div class="{{ $bubbleClass }}">
                            @if($msg->type === 'image')
                                @php
                                    $metadata = $msg->metadata ?? [];
                                    $imageId = $metadata['media_id'] ?? null;
                                    $imageUrl = null;
                                    if ($imageId) {
                                        // Usar el endpoint del servidor para servir la imagen
                                        $imageUrl = route('admin.message.image', $msg->id);
                                    }
                                @endphp
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="Imagen" class="max-w-full h-auto rounded-lg mb-2" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, '_blank')" onerror="handleImageError(this, {{ $msg->id }})">
                                @else
                                    <div style="background: #202c33; border-radius: 8px; padding: 16px; margin-bottom: 8px; text-align: center;">
                                        <div style="width: 64px; height: 64px; border-radius: 12px; background: #37a9fe; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                                <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="white"/>
                                        </svg>
                                        </div>
                                        <span style="font-size: 14px; color: #e9edef; font-weight: 500;">Imagen</span>
                                    </div>
                                @endif
                                @if(!empty($content))
                                    <div class="wa-bubble-content break-all mt-2">{{ $content }}</div>
                                @endif
                            @elseif($msg->type === 'document')
                                @php
                                    $metadata = $msg->metadata ?? [];
                                    $filename = $metadata['filename'] ?? 'documento';
                                    $fileSize = isset($metadata['file_size']) ? $metadata['file_size'] : '';
                                    $fileExtension = strtoupper(pathinfo($filename, PATHINFO_EXTENSION)) ?: 'DOC';
                                @endphp
                                <div style="background: #202c33; border-radius: 8px; padding: 12px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                                    <div style="background: #f15c6d; border-radius: 8px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <span style="color: white; font-weight: 600; font-size: 12px;">{{ $fileExtension }}</span>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 14px; font-weight: 500; color: #e9edef; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $filename }}</div>
                                        <div style="font-size: 12px; color: #8696a0;">
                                            @if($fileSize)
                                                PDF • {{ $fileSize }}
                                            @else
                                                PDF
                                            @endif
                                        </div>
                                    </div>
                                    <button style="background: transparent; border: none; cursor: pointer; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #8696a0; transition: background 0.2s;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'" title="Descargar">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            @elseif(is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'button_reply' && isset($decoded['button_reply']['title']))
                                <span class="wa-btn-reply wa-bubble-content">{{ $decoded['button_reply']['title'] }}</span>
                                @if(isset($decoded['button_reply']['description']))
                                    <div style="font-size: 12px; color: #8696a0; margin-bottom: 4px;" class="wa-bubble-content">{{ \Illuminate\Support\Str::limit($decoded['button_reply']['description'], 120) }}</div>
                                @endif
                            @elseif(is_array($decoded) && isset($decoded['type']) && $decoded['type'] === 'list_reply' && isset($decoded['list_reply']['title']))
                                <div class="font-semibold mb-1 wa-bubble-content">{{ $decoded['list_reply']['title'] }}</div>
                                @if(isset($decoded['list_reply']['description']))
                                    <div style="font-size: 12px; color: #8696a0; margin-bottom: 4px;" class="wa-bubble-content">{{ \Illuminate\Support\Str::limit($decoded['list_reply']['description'], 120) }}</div>
                                @endif
                            @elseif(is_array($decoded) && isset($decoded['title']))
                                <div class="font-semibold mb-1 wa-bubble-content">{{ $decoded['title'] }}</div>
                                @if(isset($decoded['description']))
                                    <div style="font-size: 12px; color: #8696a0; margin-bottom: 4px;" class="wa-bubble-content">{{ \Illuminate\Support\Str::limit($decoded['description'], 120) }}</div>
                                @endif
                            @else
                                <span class="wa-bubble-content">{{ $content }}</span>
                            @endif
                            <div class="wa-message-time {{ $isIncoming ? 'incoming' : 'outgoing' }}">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if(!$isIncoming)
                                    <span class="wa-message-status">
                                        <svg width="16" height="10" viewBox="0 0 16 10">
                                            <path d="M15.854 0.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L8.5 7.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#53bdeb"/>
                                            <path d="M10.854 0.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L3.5 7.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#53bdeb"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-400">No hay mensajes en este chat</div>
                @endforelse
            </div>
            <!-- Formulario de envío de mensajes -->
            <div class="wa-input-container">
                <!-- Vista previa de imagen -->
                <div id="image-preview-container" class="wa-preview-container hidden">
                    <div class="wa-preview-box">
                        <img id="image-preview" src="" alt="Vista previa" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                        <button type="button" id="remove-image-preview" class="wa-preview-remove">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Vista previa de audio -->
                <div id="audio-preview-container" class="wa-preview-container hidden">
                    <div class="wa-preview-box">
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 500; color: #e9edef; margin-bottom: 4px;" id="audio-preview-name">Audio grabado</div>
                            <div style="font-size: 12px; color: #8696a0;" id="audio-preview-duration">00:00</div>
                        </div>
                        <audio id="audio-preview-player" controls style="flex: 1; max-width: 200px; height: 32px;"></audio>
                        <button type="button" id="remove-audio-preview" class="wa-preview-remove">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Vista previa de documento -->
                <div id="document-preview-container" class="wa-preview-container hidden">
                    <div class="wa-preview-box">
                        <div style="background: #8b5cf6; border-radius: 8px; padding: 12px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="white"/>
                            </svg>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 14px; font-weight: 500; color: #e9edef; margin-bottom: 4px;" id="document-preview-name">Documento</div>
                            <div style="font-size: 12px; color: #8696a0;" id="document-preview-size">0 KB</div>
                        </div>
                        <button type="button" id="remove-document-preview" class="wa-preview-remove">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <!-- Selector de emojis (fuera del formulario para mejor posicionamiento) -->
                <div id="emoji-picker" class="absolute bottom-full left-4 mb-2 rounded-lg shadow-2xl p-2 w-80 max-h-64 overflow-hidden z-[100] hidden" style="background: #202c33; border: 1px solid #313d45;">
                    <div class="overflow-y-auto max-h-60" style="scrollbar-width: thin;">
                        <div class="grid grid-cols-9 gap-0.5 text-xl">
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😀" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😀</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😃" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😃</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😄" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😄</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😁" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😁</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😅" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😅</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😂" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😂</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="🤣" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">🤣</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😊" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😊</button>
                                <button type="button" class="emoji-btn rounded p-1" data-emoji="😇" style="transition: background 0.15s; cursor: pointer;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">😇</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙂">🙂</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙃">🙃</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😉">😉</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😌">😌</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😍">😍</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥰">🥰</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😘">😘</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😗">😗</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😙">😙</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😚">😚</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😋">😋</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😛">😛</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😝">😝</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😜">😜</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤪">🤪</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤨">🤨</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🧐">🧐</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤓">🤓</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😎">😎</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤩">🤩</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥳">🥳</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😏">😏</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😒">😒</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😞">😞</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😔">😔</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😟">😟</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😕">😕</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙁">🙁</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😣">😣</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😖">😖</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😫">😫</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😩">😩</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥺">🥺</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😢">😢</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😭">😭</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😤">😤</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😠">😠</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😡">😡</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤬">🤬</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤯">🤯</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😳">😳</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥵">🥵</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥶">🥶</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😱">😱</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😨">😨</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😰">😰</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😥">😥</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😓">😓</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤗">🤗</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤔">🤔</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤭">🤭</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤫">🤫</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤥">🤥</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😶">😶</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😐">😐</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😑">😑</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😬">😬</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙄">🙄</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😯">😯</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😦">😦</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😧">😧</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😮">😮</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😲">😲</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥱">🥱</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😴">😴</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤤">🤤</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😪">😪</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😵">😵</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤐">🤐</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🥴">🥴</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤢">🤢</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤮">🤮</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤧">🤧</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😷">😷</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤒">🤒</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤕">🤕</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤑">🤑</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤠">🤠</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😈">😈</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👿">👿</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👹">👹</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👺">👺</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤡">🤡</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="💩">💩</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👻">👻</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="💀">💀</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="☠️">☠️</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👽">👽</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👾">👾</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🤖">🤖</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🎃">🎃</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😺">😺</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😸">😸</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😹">😹</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😻">😻</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😼">😼</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😽">😽</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙀">🙀</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😿">😿</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="😾">😾</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👍">👍</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👎">👎</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="❤️">❤️</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🔥">🔥</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🎉">🎉</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="✅">✅</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="❌">❌</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="👏">👏</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🙏">🙏</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="💪">💪</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🎯">🎯</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="💰">💰</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🚀">🚀</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="⭐">⭐</button>
                                <button type="button" class="emoji-btn hover:bg-gray-100 rounded p-1" data-emoji="🎁">🎁</button>
                        </div>
                    </div>
                </div>
                <form id="send-message-form">
                    @csrf
                    <input type="hidden" name="contact_id" value="{{ $contact->id }}">
                    <input type="file" id="image-input" name="image" accept="image/*" class="hidden">
                    <input type="file" id="document-input" name="document" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip,.rar" class="hidden">
                    <input type="hidden" id="current-contact-id" value="{{ $contact->id }}">

                    <div class="wa-input-wrapper">
                    <!-- Botón de emojis -->
                    <button
                        type="button"
                        id="emoji-button"
                            class="wa-input-button"
                        title="Agregar emoji"
                    >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" fill="currentColor"/>
                                <path d="M8.5 10.5c-.828 0-1.5-.895-1.5-2s.672-2 1.5-2 1.5.895 1.5 2-.672 2-1.5 2zm7 0c-.828 0-1.5-.895-1.5-2s.672-2 1.5-2 1.5.895 1.5 2-.672 2-1.5 2zM12 18c2.28 0 4.22-1.66 5-4H7c.78 2.34 2.72 4 5 4z" fill="currentColor"/>
                        </svg>
                    </button>

                        <!-- Botón de adjuntar (menú) -->
                    <button
                        type="button"
                            id="attach-button"
                            class="wa-input-button"
                            title="Adjuntar"
                        >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z" fill="currentColor"/>
                        </svg>
                    </button>

                        <!-- Menú de adjuntar -->
                        <div id="attach-menu" class="hidden absolute bottom-full mb-2 rounded-lg shadow-xl p-2 z-50" style="min-width: 240px; left: 0; background: #233138; border: 1px solid #313d45;">
                            <button type="button" id="image-button" class="w-full text-left px-4 py-3 text-sm rounded flex items-center gap-3" style="color: #e9edef; transition: background 0.15s;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #37a9fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="white"/>
                        </svg>
                                </div>
                                <span style="font-size: 14.5px;">Fotos y videos</span>
                    </button>
                            <button type="button" id="document-button" class="w-full text-left px-4 py-3 text-sm rounded flex items-center gap-3" style="color: #e9edef; transition: background 0.15s;" onmouseover="this.style.background='#313d45'" onmouseout="this.style.background='transparent'">
                                <div style="width: 40px; height: 40px; border-radius: 10px; background: #8b5cf6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" fill="white"/>
                        </svg>
                                </div>
                                <span style="font-size: 14.5px;">Documento</span>
                    </button>
                        </div>

                        <!-- Textarea -->
                        <textarea
                            id="message-input"
                            name="message"
                            rows="1"
                            placeholder="Escribe un mensaje"
                            class="wa-input-textarea"
                        ></textarea>

                        <!-- Botón de enviar -->
                    <button
                        type="submit"
                        id="send-button"
                            class="wa-send-button"
                            title="Enviar"
                            onclick="console.log('🔘 Botón de enviar clickeado'); return true;"
                    >
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M1.101 21.757L23.8 12.028 1.101 2.3l.011 7.912 13.623 1.816-13.623 1.817-.011 7.912z" fill="currentColor"/>
                        </svg>
                    </button>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Stats Panel -->
<div class="stats-panel" id="stats-panel">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">
                📊 Total Mensajes
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Total de mensajes intercambiados con este contacto desde el inicio. Incluye todos los mensajes enviados y recibidos.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-total-messages">{{ $stats['totalMessages'] ?? 0 }}</div>
            <div class="stat-change {{ ($stats['messageGrowth'] ?? 0) >= 0 ? 'positive' : 'negative' }}" id="stat-message-growth">
                {{ ($stats['messageGrowth'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($stats['messageGrowth'] ?? 0, 1) }}% vs mes anterior
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                ⚡ Última Actividad
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Tiempo transcurrido desde el último mensaje enviado o recibido en este chat. Muestra cuándo fue la última interacción.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-last-activity" style="font-size: 0.9rem;">{{ $stats['lastActivity'] ?? 'Nunca' }}</div>
            <div class="stat-change positive" id="stat-last-activity-date">{{ $stats['lastActivityDate'] ?? 'N/A' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                ⏱️ Tiempo Promedio Respuesta
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Tiempo promedio que tarda el sistema en responder a los mensajes del cliente. Calculado desde que el cliente envía un mensaje hasta que el sistema responde.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-avg-time">{{ $stats['avgResponseTime'] ?? '0m' }}</div>
            <div class="stat-change negative">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                💬 Enviados vs Recibidos
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Comparación entre mensajes enviados por el sistema y mensajes recibidos del cliente. El ratio indica si se envían más mensajes de los que se reciben.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-sent-received">{{ ($stats['sentMessages'] ?? 0) . ' / ' . ($stats['receivedMessages'] ?? 0) }}</div>
            <div class="stat-change positive" id="stat-ratio">Ratio: {{ $stats['sentReceivedRatio'] ?? 0 }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📈 Tasa Respuesta Cliente
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Porcentaje de mensajes del sistema a los que el cliente responde dentro de 24 horas. Indica el nivel de engagement del cliente.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-client-response-rate">{{ $stats['clientResponseRate'] ?? '0%' }}</div>
            <div class="stat-change positive">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                🕐 Hora Pico
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Hora del día en la que hay mayor actividad de mensajes. Útil para identificar los mejores momentos para contactar al cliente.</span>
                </span>
        </div>
            <div class="stat-value" id="stat-peak-hour">{{ $stats['peakHour'] ?? 'N/A' }}</div>
            <div class="stat-change positive" id="stat-active-day">Día: {{ $stats['mostActiveDay'] ?? 'N/A' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📝 Longitud Promedio
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Número promedio de caracteres en los mensajes enviados por el cliente. Ayuda a entender el estilo de comunicación del cliente.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-avg-length">{{ $stats['avgMessageLength'] ?? '0 caracteres' }}</div>
            <div class="stat-change positive">Mensajes del cliente</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                💭 Conversaciones
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Número total de sesiones de conversación. Se cuenta como nueva conversación cuando pasan más de 2 horas sin mensajes.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-conversations">{{ $stats['conversations'] ?? 0 }}</div>
            <div class="stat-change positive">Total histórico</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                ⏳ Tiempo Entre Mensajes
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Tiempo promedio que pasa el cliente entre enviar un mensaje y el siguiente. Indica la velocidad de respuesta del cliente.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-time-between">{{ $stats['avgTimeBetweenMessages'] ?? '0m' }}</div>
            <div class="stat-change positive">Promedio del cliente</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📅 Frecuencia Diaria
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Promedio de mensajes por día activo. Calculado dividiendo el total de mensajes entre los días en los que hubo actividad.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-frequency">{{ $stats['frequencyPerDay'] ?? 0 }}</div>
            <div class="stat-change positive">Mensajes por día activo</div>
        </div>
    </div>

    <!-- Toggle para vista global vs contacto -->
    <div style="margin: 10px 15px; display: flex; align-items: center; gap: 15px; padding: 10px; background: #202c33; border-radius: 8px;">
        <span style="color: #e9edef; font-weight: 500;">Vista:</span>
        <button id="view-toggle" style="padding: 8px 16px; background: #005c4b; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 500; transition: all 0.2s;" onclick="toggleView()">
            <span id="view-mode-text">Contacto Actual</span>
        </button>
        <span id="view-indicator" style="color: #8696a0; font-size: 0.9rem;">Mostrando estadísticas del contacto seleccionado</span>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <canvas id="messagesChart"></canvas>
        </div>
        <div class="chart-card">
            <canvas id="responseTimeChart"></canvas>
        </div>
        <div class="chart-card">
            <canvas id="messageTypesChart"></canvas>
        </div>
        <div class="chart-card">
            <canvas id="topicsChart"></canvas>
        </div>
    </div>

    <!-- Indicadores adicionales en tarjetas -->
    <div class="stats-grid" style="margin-top: 20px;">
        <div class="stat-card">
            <div class="stat-title">
                📤 Mensajes Enviados
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Total de mensajes enviados por el sistema al cliente en los últimos 30 días.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-sent-only">{{ $stats['sentMessages'] ?? 0 }}</div>
            <div class="stat-change positive">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📥 Mensajes Recibidos
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Total de mensajes recibidos del cliente en los últimos 30 días.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-received-only">{{ $stats['receivedMessages'] ?? 0 }}</div>
            <div class="stat-change positive">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📊 Ratio Enviado/Recibido
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Proporción entre mensajes enviados y recibidos. Un valor mayor a 1 indica que se envían más mensajes de los que se reciben.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-ratio-only">{{ $stats['sentReceivedRatio'] ?? 0 }}</div>
            <div class="stat-change {{ ($stats['sentReceivedRatio'] ?? 0) > 1 ? 'positive' : 'negative' }}">
                {{ ($stats['sentReceivedRatio'] ?? 0) > 1 ? 'Más enviados' : 'Más recibidos' }}
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                🎯 Mensajes con Botones
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Porcentaje de mensajes que incluyen botones interactivos o listas desplegables. Los mensajes interactivos mejoran la experiencia del usuario.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-buttons">{{ $stats['buttonMessagesRate'] ?? '0%' }}</div>
            <div class="stat-change positive">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                💬 Tasa de Interacción
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Porcentaje de mensajes enviados por el sistema que generan una respuesta del cliente. Mide la efectividad de la comunicación.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-interaction">{{ $stats['interactionRate'] ?? '0%' }}</div>
            <div class="stat-change positive">Últimos 30 días</div>
        </div>
        <div class="stat-card">
            <div class="stat-title">
                📈 Crecimiento Mensajes
                <span class="stat-info-icon" title="Información">
                    ℹ️
                    <span class="stat-tooltip">Porcentaje de crecimiento en el número de mensajes comparado con el mes anterior. Un valor positivo indica aumento en la actividad.</span>
                </span>
            </div>
            <div class="stat-value" id="stat-growth-value">{{ ($stats['messageGrowth'] ?? 0) >= 0 ? '+' : '' }}{{ number_format($stats['messageGrowth'] ?? 0, 1) }}%</div>
            <div class="stat-change {{ ($stats['messageGrowth'] ?? 0) >= 0 ? 'positive' : 'negative' }}">vs mes anterior</div>
        </div>
    </div>

    <!-- Tipos de Mensajes Section -->
    <div class="chart-card mt-4" id="message-types-list">
        <h3 class="text-lg font-semibold mb-3" style="color: #e9edef;">Tipos de Mensajes</h3>
        <div class="topics-list" id="topics-list">
            @if(isset($stats['messageTypes']) && count($stats['messageTypes']) > 0)
                @foreach($stats['messageTypes'] as $type => $count)
            <div class="topic-item">
                    <span class="topic-name">{{ ucfirst($type ?: 'texto') }}</span>
                    <span class="topic-count">{{ $count }}</span>
            </div>
            @endforeach
            @else
                <div class="topic-item">
                    <span class="topic-name">Sin datos disponibles</span>
                    <span class="topic-count">0</span>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Scroll automático al último mensaje
    window.onload = function() {
        var chat = document.getElementById('chat-messages');
        if(chat) chat.scrollTop = chat.scrollHeight;
    };

    // Variables globales para polling
    let pollingInterval = null;
    let lastMessageTimestamp = null;
    let lastMessageId = 0;
    let currentPollingContactId = null;

    // Función para agregar mensajes a la vista (debe estar fuera de DOMContentLoaded para ser accesible globalmente)
    function addMessageToView(messageData, isIncoming = false) {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) {
            console.error('Chat messages container not found');
            return;
        }

        const messageDiv = document.createElement('div');
        messageDiv.className = isIncoming ? 'flex justify-start' : 'flex justify-end';
        messageDiv.setAttribute('data-message-id', messageData.id || Date.now());

        // Usar la fecha del mensaje si está disponible, sino la fecha actual
        const messageDate = messageData.created_at ? new Date(messageData.created_at) : new Date();
        const formattedDate = messageDate.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        const timeOnly = messageData.created_at_formatted || messageDate.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });

        let contentHtml = '';

        // Si tiene imagen
        if (messageData.type === 'image' && messageData.id) {
            const imageUrl = `/admin/messages/${messageData.id}/image`;
            contentHtml += `<img src="${imageUrl}" alt="Imagen" class="max-w-full h-auto rounded-lg mb-2" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, '_blank')" onerror="handleImageError(this, ${messageData.id})">`;
        } else if (messageData.image_url || messageData.image_data) {
            const imageUrl = messageData.image_url || (messageData.image_data ? 'data:image/jpeg;base64,' + messageData.image_data : '');
            if (imageUrl) {
                contentHtml += `<img src="${imageUrl}" alt="Imagen" class="max-w-full h-auto rounded-lg mb-2" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, '_blank')">`;
            }
        }

        // Si tiene documento
        if (messageData.type === 'document') {
            const filename = messageData.filename || messageData.content || 'documento';
            contentHtml += `<div class="bg-gray-200 rounded-lg p-4 mb-2 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm">📄 ${escapeHtmlGlobal(filename)}</span>
                </div>`;
        }

        // Si tiene mensaje de texto
        if (messageData.content) {
            contentHtml += `<span class="wa-bubble-content break-all">${escapeHtmlGlobal(messageData.content)}</span>`;
        }

        // Determinar la clase de burbuja según el tipo de mensaje
        const bubbleClass = isIncoming ? 'wa-bubble-in' : 'wa-bubble-out';
        const badgeHtml = isIncoming
            ? '<div class="wa-badge wa-badge-cliente"><span>👤</span>Cliente</div>'
            : '<div class="wa-badge wa-badge-sistema"><span>🤖</span>Sistema</div>';

        messageDiv.innerHTML = `
            <div class="max-w-[70%] w-full break-words overflow-x-auto px-4 py-2 mb-1 ${bubbleClass} relative">
                ${badgeHtml}
                ${contentHtml}
                <div class="text-[10px] text-gray-400 ${isIncoming ? 'text-left' : 'text-right'} mt-1">${timeOnly}</div>
            </div>
        `;

        chatMessages.appendChild(messageDiv);
    }

    // Función helper para escape HTML (versión global)
    function escapeHtmlGlobal(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Sistema de polling para obtener nuevos mensajes automáticamente
    function startPolling(contactId) {
        // Detener polling anterior si existe
        stopPolling();

        currentPollingContactId = contactId;

        // Obtener el timestamp del último mensaje visible
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            const lastMessage = chatMessages.querySelector('[data-message-id]');
            if (lastMessage) {
                lastMessageId = parseInt(lastMessage.getAttribute('data-message-id')) || 0;
            }
        }

        // Iniciar polling cada 2 segundos
        pollingInterval = setInterval(() => {
            if (currentPollingContactId) {
                checkForNewMessages(currentPollingContactId);
            }
        }, 2000); // Polling cada 2 segundos

        // Hacer una verificación inmediata
        checkForNewMessages(contactId);
    }

    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        currentPollingContactId = null;
    }

    function checkForNewMessages(contactId) {
        if (!contactId) return;

        const params = new URLSearchParams();
        if (lastMessageId > 0) {
            params.append('last_message_id', lastMessageId);
        }
        if (lastMessageTimestamp) {
            params.append('last_timestamp', lastMessageTimestamp);
        }

        fetch(`/admin/chats/${contactId}/new-messages?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parseando respuesta:', text);
                return { success: false };
            }
        }))
        .then(data => {
            if (data.success && data.messages && data.messages.length > 0) {
                const chatMessages = document.getElementById('chat-messages');
                const existingMessageIds = new Set();

                // Obtener IDs de mensajes existentes
                chatMessages.querySelectorAll('[data-message-id]').forEach(msg => {
                    existingMessageIds.add(parseInt(msg.getAttribute('data-message-id')));
                });

                // Agregar solo mensajes nuevos
                let hasNewMessages = false;
                data.messages.forEach(messageData => {
                    const msgId = parseInt(messageData.id);
                    if (!existingMessageIds.has(msgId)) {
                        addMessageToView(messageData, messageData.sender_type === 'client');
                        hasNewMessages = true;

                        // Actualizar último mensaje
                        if (msgId > lastMessageId) {
                            lastMessageId = msgId;
                        }
                        if (!lastMessageTimestamp || new Date(messageData.created_at) > new Date(lastMessageTimestamp)) {
                            lastMessageTimestamp = messageData.created_at;
                        }
                    }
                });

                // Guardar el último mensaje visto cuando llegan nuevos mensajes
                if (hasNewMessages && data.messages && data.messages.length > 0) {
                    const lastClientMessage = data.messages.filter(msg => msg.sender_type === 'client').pop();
                    if (lastClientMessage) {
                        localStorage.setItem(`last_seen_${contactId}`, lastClientMessage.created_at);
                    }
                }

                // Scroll al final si hay mensajes nuevos y el usuario está cerca del final
                if (hasNewMessages) {
                    setTimeout(() => {
                        const chatMessages = document.getElementById('chat-messages');
                        if (chatMessages) {
                            const isNearBottom = chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight < 200;
                            if (isNearBottom) {
                                chatMessages.scrollTop = chatMessages.scrollHeight;
                            }
                        }
                    }, 100);
                }
            }
        })
        .catch(error => {
            console.error('Error obteniendo nuevos mensajes:', error);
        });
    }

    // Scroll infinito (cargar más mensajes al llegar arriba)
    document.addEventListener('DOMContentLoaded', function() {
        var chat = document.getElementById('chat-messages');
        if(chat) {
            chat.addEventListener('scroll', function() {
                if(chat.scrollTop === 0) {
                    // Aquí puedes hacer una petición AJAX para cargar más mensajes
                    // y agregarlos al principio del contenedor
                }
            });
        }

        // Manejar envío de mensajes
        const messageForm = document.getElementById('send-message-form');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');

        // Verificar que los elementos existan - SI NO EXISTEN, SALIR
        if (!messageForm || !messageInput || !sendButton) {
            console.error('No se encontraron los elementos del formulario de envío');
            return; // Salir si no existen los elementos críticos
        }

        // VARIABLES GLOBALES PARA EL FORMULARIO
        let selectedImageFile = null;
        let selectedDocumentFile = null;

        // REGISTRAR EL EVENT LISTENER DEL FORMULARIO PRIMERO - CRÍTICO
        messageForm.addEventListener('submit', function(e) {
            console.log('=== INICIO ENVÍO MENSAJE ===');
            console.log('1. Formulario submit interceptado', e);
            e.preventDefault();
            e.stopPropagation();
            console.log('2. preventDefault() ejecutado');

            const message = messageInput.value.trim();
            const hasMessage = message.length > 0;
            const hasImage = selectedImageFile !== null;
            const hasDocument = selectedDocumentFile !== null;

            console.log('3. Validación de contenido:', {
                message: message,
                hasMessage: hasMessage,
                hasImage: hasImage,
                hasDocument: hasDocument,
                messageLength: message.length
            });

            if (!hasMessage && !hasImage && !hasDocument) {
                console.warn('4. ❌ No hay contenido para enviar - ABORTANDO');
                return false;
            }

            const contactIdInput = document.querySelector('input[name="contact_id"]');
            const csrfTokenInput = document.querySelector('input[name="_token"]');

            console.log('5. Buscando campos del formulario:', {
                contactIdInput: contactIdInput ? 'encontrado' : 'NO ENCONTRADO',
                csrfTokenInput: csrfTokenInput ? 'encontrado' : 'NO ENCONTRADO'
            });

            if (!contactIdInput || !csrfTokenInput) {
                console.error('6. ❌ No se encontraron los campos necesarios del formulario');
                alert('Error: No se pudo obtener la información del formulario');
                return false;
            }

            const contactId = contactIdInput.value;
            const csrfToken = csrfTokenInput.value;

            console.log('6. Valores obtenidos:', {
                contactId: contactId,
                csrfToken: csrfToken ? 'presente (' + csrfToken.substring(0, 10) + '...)' : 'NO ENCONTRADO'
            });

            if (!contactId) {
                console.error('6a. ❌ No se ha seleccionado un contacto');
                alert('Error: No se ha seleccionado un contacto');
                return false;
            }

            // Deshabilitar el botón mientras se envía
            console.log('7. Deshabilitando botón de envío');
            sendButton.disabled = true;
            const originalButtonContent = sendButton.innerHTML;
            sendButton.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="animate-spin"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"></circle><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            // Preparar FormData para enviar archivos
            console.log('8. Preparando FormData');
            const formData = new FormData();
            formData.append('contact_id', contactId);
            formData.append('_token', csrfToken);
            if (hasMessage) {
                formData.append('message', message);
                console.log('   - Mensaje agregado:', message.substring(0, 50) + (message.length > 50 ? '...' : ''));
            }
            if (hasImage) {
                formData.append('image', selectedImageFile);
                console.log('   - Imagen agregada:', selectedImageFile.name, selectedImageFile.size, 'bytes');
            }
            if (hasDocument) {
                formData.append('document', selectedDocumentFile);
                console.log('   - Documento agregado:', selectedDocumentFile.name, selectedDocumentFile.size, 'bytes');
            }

            const url = '{{ route("admin.chat.send") }}';
            console.log('9. Preparando petición fetch:', {
                url: url,
                method: 'POST',
                contactId: contactId,
                hasMessage: hasMessage,
                hasImage: hasImage,
                hasDocument: hasDocument
            });

            console.log('10. Iniciando fetch...');
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                console.log('11. ✅ Respuesta HTTP recibida:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    headers: Object.fromEntries(response.headers.entries())
                });

                // Intentar parsear la respuesta como JSON
                return response.text().then(text => {
                    console.log('12. Texto de respuesta recibido:', text.substring(0, 200) + (text.length > 200 ? '...' : ''));
                    try {
                        const parsed = JSON.parse(text);
                        console.log('13. ✅ JSON parseado correctamente:', parsed);
                        return parsed;
                    } catch (e) {
                        console.error('13. ❌ Error parseando JSON:', e);
                        console.error('   Texto recibido:', text);
                        throw new Error('La respuesta del servidor no es válida');
                    }
                }).then(data => {
                    if (!response.ok) {
                        console.error('14. ❌ Respuesta HTTP no OK:', {
                            status: response.status,
                            data: data
                        });
                        throw new Error(data.message || 'Error en la respuesta del servidor');
                    }
                    console.log('14. ✅ Respuesta OK, datos:', data);
                    return data;
                });
            })
            .then(data => {
                console.log('15. Procesando respuesta exitosa:', data);
                if (data.success) {
                    console.log('16. ✅ Mensaje enviado exitosamente');
                    // Limpiar el input
                    messageInput.value = '';
                    messageInput.style.height = 'auto';

                    // Limpiar archivos
                    selectedImageFile = null;
                    selectedDocumentFile = null;
                    const imageInput = document.getElementById('image-input');
                    const documentInput = document.getElementById('document-input');
                    const imagePreviewContainer = document.getElementById('image-preview-container');
                    const documentPreviewContainer = document.getElementById('document-preview-container');
                    const imagePreview = document.getElementById('image-preview');
                    if (imageInput) imageInput.value = '';
                    if (documentInput) documentInput.value = '';
                    if (imagePreviewContainer) imagePreviewContainer.classList.add('hidden');
                    if (documentPreviewContainer) documentPreviewContainer.classList.add('hidden');
                    if (imagePreview) imagePreview.src = '';

                    // Agregar el mensaje a la vista
                    if (data.message) {
                        console.log('17. Agregando mensaje a la vista:', data.message);
                        addMessageToView(data.message);

                        // Scroll al final después de agregar el mensaje
                        setTimeout(() => {
                            const chatMessages = document.getElementById('chat-messages');
                            if (chatMessages) {
                                console.log('18. Haciendo scroll al final del chat');
                                // Forzar scroll al final
                                chatMessages.scrollTop = chatMessages.scrollHeight;
                                // Asegurar que el scroll funcione
                                requestAnimationFrame(() => {
                                    chatMessages.scrollTop = chatMessages.scrollHeight;
                                });
                            }
                        }, 100);
                    } else {
                        console.warn('17. ⚠️ No hay mensaje en la respuesta para mostrar');
                    }
                } else {
                    console.error('16. ❌ Respuesta indica fallo:', data);
                    alert('Error al enviar el mensaje: ' + (data.message || 'Error desconocido'));
                }
            })
            .catch(error => {
                console.error('=== ERROR EN ENVÍO ===');
                console.error('Error completo:', error);
                console.error('Stack trace:', error.stack);
                console.error('Mensaje:', error.message);
                alert('Error al enviar el mensaje: ' + (error.message || 'Por favor, intenta nuevamente.'));
            })
            .finally(() => {
                console.log('19. Restaurando botón de envío');
                sendButton.disabled = false;
                sendButton.innerHTML = originalButtonContent;
                if (messageInput) messageInput.focus();
                console.log('=== FIN ENVÍO MENSAJE ===');
            });

            return false;
        });
        console.log('✅ Event listener del formulario registrado correctamente');
        console.log('Formulario:', messageForm);
        console.log('Input mensaje:', messageInput);
        console.log('Botón enviar:', sendButton);

        const emojiButton = document.getElementById('emoji-button');
        const emojiPicker = document.getElementById('emoji-picker');
        const attachButton = document.getElementById('attach-button');
        const attachMenu = document.getElementById('attach-menu');
        const imageButton = document.getElementById('image-button');
        const imageInput = document.getElementById('image-input');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById('image-preview');
        const removeImagePreview = document.getElementById('remove-image-preview');
        const documentButton = document.getElementById('document-button');
        const documentInput = document.getElementById('document-input');
        const documentPreviewContainer = document.getElementById('document-preview-container');
        const documentPreviewName = document.getElementById('document-preview-name');
        const documentPreviewSize = document.getElementById('document-preview-size');
        const removeDocumentPreview = document.getElementById('remove-document-preview');
        const currentContactId = document.getElementById('current-contact-id');

        // Función para cerrar el selector de emojis
        function closeEmojiPicker() {
            if (emojiPicker) {
                emojiPicker.classList.add('hidden');
            }
        }

        // Función para abrir/cerrar el selector de emojis
        function toggleEmojiPicker() {
            if (emojiPicker && emojiPicker.classList.contains('hidden')) {
                emojiPicker.classList.remove('hidden');
            } else {
                closeEmojiPicker();
            }
        }

        // Manejar selector de emojis
        if (emojiButton) {
            emojiButton.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                toggleEmojiPicker();
            });
        }

        // Manejar menú de adjuntar
        if (attachButton && attachMenu) {
            attachButton.addEventListener('click', function(e) {
                e.stopPropagation();
                attachMenu.classList.toggle('hidden');
                closeEmojiPicker();
            });
        }

        // Cerrar menús al hacer clic fuera
        document.addEventListener('click', function(e) {
            if (emojiButton && emojiPicker && !emojiButton.contains(e.target) && !emojiPicker.contains(e.target)) {
                closeEmojiPicker();
            }
            if (attachButton && attachMenu && !attachButton.contains(e.target) && !attachMenu.contains(e.target)) {
                attachMenu.classList.add('hidden');
            }
        });

        // Cerrar selector al presionar Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && emojiPicker && !emojiPicker.classList.contains('hidden')) {
                closeEmojiPicker();
            }
        });

        // Insertar emoji al hacer clic
        if (messageInput) {
            document.querySelectorAll('.emoji-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const emoji = this.getAttribute('data-emoji');
                const cursorPos = messageInput.selectionStart;
                const textBefore = messageInput.value.substring(0, cursorPos);
                const textAfter = messageInput.value.substring(cursorPos);
                messageInput.value = textBefore + emoji + textAfter;
                messageInput.selectionStart = messageInput.selectionEnd = cursorPos + emoji.length;
                messageInput.focus();

                // Trigger resize
                messageInput.dispatchEvent(new Event('input'));

                // No cerrar el selector automáticamente (como WhatsApp Web)
                // closeEmojiPicker();
            });
        });
        }

        // Manejar selección de imagen
        if (imageButton && imageInput) {
            imageButton.addEventListener('click', function() {
                if (attachMenu) attachMenu.classList.add('hidden');
                // Limpiar otros archivos seleccionados
                selectedDocumentFile = null;
                if (documentInput) documentInput.value = '';
                imageInput.click();
            });

            imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) { // 5MB max
                    alert('La imagen es demasiado grande. Máximo 5MB.');
                    return;
                }

                selectedImageFile = file;
                selectedDocumentFile = null;
                if (documentInput) documentInput.value = '';
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (imagePreview) imagePreview.src = e.target.result;
                    if (imagePreviewContainer) imagePreviewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
        }

        // Remover vista previa de imagen
        if (removeImagePreview && imageInput && imagePreviewContainer && imagePreview) {
            removeImagePreview.addEventListener('click', function() {
                selectedImageFile = null;
                imageInput.value = '';
                imagePreviewContainer.classList.add('hidden');
                imagePreview.src = '';
            });
        }

        // Manejar selección de documento
        if (documentButton && documentInput && imageInput && imagePreviewContainer) {
            documentButton.addEventListener('click', function() {
                if (attachMenu) attachMenu.classList.add('hidden');
                // Limpiar otros archivos seleccionados
                selectedImageFile = null;
                imageInput.value = '';
                imagePreviewContainer.classList.add('hidden');
                documentInput.click();
            });

            documentInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB max
                    alert('El documento es demasiado grande. Máximo 10MB.');
                    return;
                }
                selectedDocumentFile = file;
                selectedImageFile = null;
                imageInput.value = '';
                imagePreviewContainer.classList.add('hidden');

                // Mostrar vista previa
                if (documentPreviewName) documentPreviewName.textContent = file.name;
                const fileSize = (file.size / 1024).toFixed(2);
                if (documentPreviewSize) documentPreviewSize.textContent = fileSize > 1024 ? `${(fileSize / 1024).toFixed(2)} MB` : `${fileSize} KB`;
                if (documentPreviewContainer) documentPreviewContainer.classList.remove('hidden');
            }
        });
        }

        // Remover vista previa de documento
        if (removeDocumentPreview && documentInput && documentPreviewContainer) {
            removeDocumentPreview.addEventListener('click', function() {
                selectedDocumentFile = null;
                documentInput.value = '';
                documentPreviewContainer.classList.add('hidden');
            });
        }

        // Auto-resize del textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });

        // Permitir Enter para enviar y Shift+Enter para nueva línea
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                console.log('Enter presionado (sin Shift) - disparando submit');
                e.preventDefault();
                if (messageInput.value.trim() || selectedImageFile || selectedDocumentFile) {
                    console.log('Contenido válido, disparando evento submit');
                    messageForm.dispatchEvent(new Event('submit'));
                } else {
                    console.log('Sin contenido válido, no se dispara submit');
                }
            }
        });

        // addMessageToView y escapeHtmlGlobal ahora están definidas globalmente (fuera de DOMContentLoaded)
        // Esta función escapeHtml local puede usar la global si es necesario dentro de este scope
        function escapeHtml(text) {
            return escapeHtmlGlobal(text);
        }

        // Manejar toggle del bot
        const botToggle = document.getElementById('bot-enabled-toggle');
        if (botToggle) {
            botToggle.addEventListener('change', function() {
                const contactId = this.getAttribute('data-contact-id');
                const enabled = this.checked;
                const statusText = document.getElementById('bot-status-text');

                // Actualizar texto inmediatamente
                if (statusText) {
                    statusText.textContent = enabled ? 'Activo' : 'Inactivo';
                }

                // Enviar petición al servidor
                fetch(`/admin/contacts/${contactId}/toggle-bot`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        enabled: enabled
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Estado del bot actualizado:', enabled ? 'Activo' : 'Inactivo');
                    } else {
                        // Revertir si falla
                        this.checked = !enabled;
                        if (statusText) {
                            statusText.textContent = !enabled ? 'Activo' : 'Inactivo';
                        }
                        alert('Error al actualizar el estado del bot: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    // Revertir si falla
                    this.checked = !enabled;
                    if (statusText) {
                        statusText.textContent = !enabled ? 'Activo' : 'Inactivo';
                    }
                    alert('Error al actualizar el estado del bot. Por favor, intenta nuevamente.');
                });
            });
        }

        // Iniciar polling cuando se carga la página
        const contactIdInput = document.querySelector('input[name="contact_id"]');
        if (contactIdInput && contactIdInput.value) {
            startPolling(parseInt(contactIdInput.value));
        }

        // Detener polling cuando el usuario sale de la página
        window.addEventListener('beforeunload', () => {
            stopPolling();
        });

        // Detener polling cuando la página pierde el foco
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else {
                const contactIdInput = document.querySelector('input[name="contact_id"]');
                if (contactIdInput && contactIdInput.value) {
                    startPolling(parseInt(contactIdInput.value));
                }
            }
        });
    });


        // Función global para cargar chat de contacto
        function loadContactChat(contactId) {
            // Actualizar URL sin recargar
            const newUrl = `/admin/chats/${contactId}`;
            window.history.pushState({contactId: contactId}, '', newUrl);

            // Mostrar indicador de carga
            const chatMessages = document.getElementById('chat-messages');
            const chatHeader = document.querySelector('.wa-chat-header');
            chatMessages.innerHTML = '<div class="text-center text-gray-400 py-8">Cargando mensajes...</div>';

            // Cargar mensajes del contacto
            fetch(`/admin/chats/${contactId}/messages`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar header
                    const headerName = chatHeader.querySelector('.wa-chat-header-name');
                    const headerStatus = chatHeader.querySelector('.wa-chat-header-status');
                    const headerAvatar = chatHeader.querySelector('.wa-chat-avatar');

                    if (headerName) headerName.textContent = data.contact.name || 'Cliente';
                    if (headerStatus) headerStatus.textContent = data.contact.phone_number || '';
                    if (headerAvatar) {
                        headerAvatar.textContent = (data.contact.name || 'C').charAt(0).toUpperCase();
                        // Asegurar que el avatar tenga el ID para el toggle
                        if (!headerAvatar.id) {
                            headerAvatar.id = 'waChatAvatarToggle';
                        }
                    }

                    // Actualizar toggle del bot
                    const botToggle = document.getElementById('bot-enabled-toggle');
                    const botStatusText = document.getElementById('bot-status-text');
                    if (botToggle && data.contact.bot_enabled !== undefined) {
                        botToggle.checked = data.contact.bot_enabled;
                        botToggle.setAttribute('data-contact-id', contactId);
                    }
                    if (botStatusText && data.contact.bot_enabled !== undefined) {
                        botStatusText.textContent = data.contact.bot_enabled ? 'Activo' : 'Inactivo';
                    }

                    // Actualizar ID del contacto en el formulario
                    const contactIdInput = document.querySelector('input[name="contact_id"]');
                    if (contactIdInput) contactIdInput.value = contactId;
                    const currentContactIdInput = document.getElementById('current-contact-id');
                    if (currentContactIdInput) currentContactIdInput.value = contactId;

                    // Reiniciar polling para el nuevo contacto
                    lastMessageId = 0;
                    lastMessageTimestamp = null;
                    startPolling(contactId);

                    // Renderizar mensajes
                    chatMessages.innerHTML = '';
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            renderMessage(msg);
                        });

                        // Actualizar último mensaje para polling
                        const lastMsg = data.messages[data.messages.length - 1];
                        if (lastMsg) {
                            lastMessageId = parseInt(lastMsg.id) || 0;
                            lastMessageTimestamp = lastMsg.created_at || null;
                        }

                        // Scroll al final
                        setTimeout(() => {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }, 100);
                    } else {
                        chatMessages.innerHTML = '<div class="text-center py-8" style="color: #8696a0;">No hay mensajes en este chat</div>';
                        lastMessageId = 0;
                        lastMessageTimestamp = null;
                    }

                    // Actualizar el ID del contacto actual en la variable global
                    currentContactId = parseInt(contactId);

                    // Actualizar estado activo en sidebar
                    document.querySelectorAll('.wa-sidebar-contact').forEach(contact => {
                        contact.classList.remove('active');
                    });
                    document.querySelector(`.wa-sidebar-contact[data-contact-id="${contactId}"]`)?.classList.add('active');

                    // Guardar el último mensaje visto para este contacto
                    if (data.messages && data.messages.length > 0) {
                        const lastClientMessage = data.messages.filter(msg => msg.sender_type === 'client').pop();
                        if (lastClientMessage) {
                            // Guardar en localStorage el timestamp del último mensaje visto
                            localStorage.setItem(`last_seen_${contactId}`, lastClientMessage.created_at);
                        } else {
                            // Si no hay mensajes del cliente, guardar la fecha actual
                            localStorage.setItem(`last_seen_${contactId}`, new Date().toISOString());
                        }
                    } else {
                        // Si no hay mensajes, guardar la fecha actual
                        localStorage.setItem(`last_seen_${contactId}`, new Date().toISOString());
                    }

                    // Remover indicador de mensaje nuevo del contacto activo
                    const activeContact = document.querySelector(`.wa-sidebar-contact[data-contact-id="${contactId}"]`);
                    if (activeContact) {
                        const newMessageIndicator = activeContact.querySelector('.wa-new-message-indicator');
                        if (newMessageIndicator) {
                            newMessageIndicator.remove();
                        }
                    }

                    // Actualizar la lista de contactos para reflejar el cambio (con un pequeño delay para asegurar que el DOM se actualizó)
                    setTimeout(() => {
                        updateContactsList();
                    }, 100);

                    // Actualizar estadísticas si están disponibles
                    if (data.stats && currentViewMode === 'contact') {
                        updateStats(data.stats);
                        updateCharts(data.stats);
                        updateMessageTypesList(data.stats.messageTypes || {});
                    }

                    // Scroll al final
                    setTimeout(() => {
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 100);
                }
            })
            .catch(error => {
                console.error('Error cargando mensajes:', error);
                chatMessages.innerHTML = '<div class="text-center py-8" style="color: #f15c6d;">Error al cargar los mensajes</div>';
            });
        }

        // Agregar event listeners a los contactos originales
        function attachContactListeners() {
            document.querySelectorAll('.wa-sidebar-contact[data-contact-id]').forEach(link => {
                // Remover listeners anteriores si existen
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);

                newLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const contactId = this.getAttribute('data-contact-id');
                    if (contactId) {
                        loadContactChat(contactId);
                    }
                });
            });
        }

        // Agregar listeners cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachContactListeners);
        } else {
            attachContactListeners();
        }

        function renderMessage(msg) {
            const chatMessages = document.getElementById('chat-messages');
            const isIncoming = msg.sender_type === 'client';
            const bubbleClass = isIncoming ? 'wa-bubble-in' : 'wa-bubble-out';
            const wrapperClass = isIncoming ? 'incoming' : 'outgoing';

            let contentHtml = '';
            const content = msg.content || '';
            let decoded = null;

            try {
                decoded = JSON.parse(content);
            } catch (e) {
                decoded = null;
            }

            if (msg.type === 'image') {
                const imageUrl = `/admin/messages/${msg.id}/image`;
                contentHtml += `<img src="${imageUrl}" alt="Imagen" class="max-w-full h-auto rounded-lg mb-2" style="max-height: 300px; cursor: pointer;" onclick="window.open(this.src, '_blank')" onerror="handleImageError(this, ${msg.id})">`;
                if (content && !decoded) {
                    contentHtml += `<div class="wa-bubble-content break-all mt-2">${escapeHtml(content)}</div>`;
                }
            } else if (msg.type === 'document') {
                const metadata = msg.metadata || {};
                const filename = metadata.filename || 'documento';
                const fileSize = metadata.file_size || '';
                const fileExtension = filename.split('.').pop()?.toUpperCase() || 'DOC';
                contentHtml += `<div style="background: #202c33; border-radius: 8px; padding: 12px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                    <div style="background: #f15c6d; border-radius: 8px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span style="color: white; font-weight: 600; font-size: 12px;">${fileExtension}</span>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 500; color: #e9edef; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(filename)}</div>
                        <div style="font-size: 12px; color: #8696a0;">
                            ${fileSize ? `PDF • ${fileSize}` : 'PDF'}
                        </div>
                    </div>
                    <button style="background: transparent; border: none; cursor: pointer; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #8696a0;" title="Descargar">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>`;
            } else if (decoded && decoded.type === 'button_reply' && decoded.button_reply) {
                contentHtml += `<span class="wa-btn-reply wa-bubble-content">${escapeHtml(decoded.button_reply.title)}</span>`;
            } else if (decoded && decoded.type === 'list_reply' && decoded.list_reply) {
                contentHtml += `<div class="font-semibold mb-1 wa-bubble-content">${escapeHtml(decoded.list_reply.title)}</div>`;
            } else if (content) {
                contentHtml += `<span class="wa-bubble-content">${escapeHtml(content)}</span>`;
            }

            const time = new Date(msg.created_at).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
            const statusIcon = !isIncoming ? '<svg width="16" height="10" viewBox="0 0 16 10"><path d="M15.854 0.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L8.5 7.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#53bdeb"/><path d="M10.854 0.146a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L3.5 7.293l6.646-6.647a.5.5 0 0 1 .708 0z" fill="#53bdeb"/></svg>' : '';

            const messageDiv = document.createElement('div');
            messageDiv.className = `wa-message-wrapper ${wrapperClass}`;
            messageDiv.setAttribute('data-message-id', msg.id);
            messageDiv.innerHTML = `
                <div class="${bubbleClass}">
                    ${contentHtml}
                    <div class="wa-message-time ${isIncoming ? 'incoming' : 'outgoing'}">
                        <span>${time}</span>
                        ${statusIcon}
                    </div>
                </div>
            `;
            chatMessages.appendChild(messageDiv);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Variables globales para los gráficos
        let messagesChart = null;
        let responseTimeChart = null;
        let messageTypesChart = null;
        let topicsChart = null;

        // Función para inicializar/actualizar gráficos
        function updateCharts(stats) {
            const messagesByDay = stats.messagesByDay || [];
            const responseTimeByDay = stats.responseTimeByDay || [];
            const messageTypes = stats.messageTypes || {};

            // Destruir gráficos existentes si existen
            if (messagesChart) {
                try { messagesChart.destroy(); } catch(e) { console.warn('Error destruyendo messagesChart:', e); }
                messagesChart = null;
            }
            if (responseTimeChart) {
                try { responseTimeChart.destroy(); } catch(e) { console.warn('Error destruyendo responseTimeChart:', e); }
                responseTimeChart = null;
            }
            if (messageTypesChart) {
                try { messageTypesChart.destroy(); } catch(e) { console.warn('Error destruyendo messageTypesChart:', e); }
                messageTypesChart = null;
            }
            if (topicsChart) {
                try { topicsChart.destroy(); } catch(e) { console.warn('Error destruyendo topicsChart:', e); }
                topicsChart = null;
            }

            // Message activity chart
            const messagesCtx = document.getElementById('messagesChart');
            if (messagesCtx && typeof Chart !== 'undefined') {
                messagesChart = new Chart(messagesCtx.getContext('2d'), {
            type: 'line',
            data: {
                        labels: messagesByDay.map(d => d.day),
                datasets: [{
                    label: 'Mensajes Enviados',
                            data: messagesByDay.map(d => d.sent || 0),
                    borderColor: '#25d366',
                            backgroundColor: 'rgba(37, 211, 102, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Mensajes Recibidos',
                            data: messagesByDay.map(d => d.received || 0),
                    borderColor: '#128C7E',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                                text: 'Actividad de Mensajes (Últimos 7 días)'
                            },
                            legend: {
                                labels: {
                                    color: '#8696a0'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                            },
                            y: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                    }
                }
            }
        });
            }

        // Response time chart
            const responseCtx = document.getElementById('responseTimeChart');
            if (responseCtx && typeof Chart !== 'undefined') {
                responseTimeChart = new Chart(responseCtx.getContext('2d'), {
            type: 'bar',
            data: {
                        labels: responseTimeByDay.map(d => d.day),
                datasets: [{
                    label: 'Tiempo de Respuesta (min)',
                            data: responseTimeByDay.map(d => d.time || 0),
                    backgroundColor: '#25d366'
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                                text: 'Tiempo Promedio de Respuesta (Últimos 7 días)'
                            },
                            legend: {
                                labels: {
                                    color: '#8696a0'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                            },
                            y: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                    }
                }
            }
        });
            }

        // Message Types Distribution
            const messageTypesCtx = document.getElementById('messageTypesChart');
            if (messageTypesCtx && typeof Chart !== 'undefined') {
                const typeLabels = Object.keys(messageTypes);
                const typeData = Object.values(messageTypes);
                const colors = ['#25d366', '#128C7E', '#34B7F1', '#F15C6D', '#8b5cf6', '#37a9fe'];

                messageTypesChart = new Chart(messageTypesCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                        labels: typeLabels.length > 0 ? typeLabels : ['Sin datos'],
                datasets: [{
                            data: typeData.length > 0 ? typeData : [1],
                            backgroundColor: colors.slice(0, Math.max(typeLabels.length, 1))
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Distribución de Tipos de Mensajes'
                            },
                            legend: {
                                labels: {
                                    color: '#8696a0'
                                }
                    }
                }
            }
        });
            }

            // Topics Distribution (usando tipos de mensajes)
            const topicsCtx = document.getElementById('topicsChart');
            if (topicsCtx && typeof Chart !== 'undefined') {
                const topicLabels = Object.keys(messageTypes).slice(0, 5);
                const topicData = Object.values(messageTypes).slice(0, 5);

                topicsChart = new Chart(topicsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                        labels: topicLabels.length > 0 ? topicLabels : ['Sin datos'],
                datasets: [{
                            label: 'Mensajes por Tipo',
                            data: topicData.length > 0 ? topicData : [0],
                    backgroundColor: '#25d366'
                }]
            },
            options: {
                responsive: true,
                        maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                                text: 'Distribución de Tipos de Mensajes'
                            },
                            legend: {
                                labels: {
                                    color: '#8696a0'
                                }
                            }
                        },
                        scales: {
                            x: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                            },
                            y: {
                                ticks: { color: '#8696a0' },
                                grid: { color: 'rgba(134, 150, 160, 0.1)' }
                    }
                }
            }
        });
            }
        }

        // Variable global para el modo de vista
        let currentViewMode = 'contact'; // 'contact' o 'global'
        let globalStats = @json($globalStats ?? []);

        // Inicializar gráficos con datos iniciales al cargar la página
        function initializeCharts() {
            // Verificar que Chart.js esté cargado
            if (typeof Chart === 'undefined') {
                console.warn('Chart.js no está cargado, reintentando en 100ms...');
                setTimeout(initializeCharts, 100);
                return;
            }

            @if(isset($stats))
            // Inicializar gráficos con datos del contacto actual
            const initialStats = @json($stats);
            if (initialStats && Object.keys(initialStats).length > 0) {
                // Asegurar que los datos necesarios existan
                if (!initialStats.messagesByDay) initialStats.messagesByDay = [];
                if (!initialStats.responseTimeByDay) initialStats.responseTimeByDay = [];
                if (!initialStats.messageTypes) initialStats.messageTypes = {};

                // Esperar a que los canvas estén disponibles
                setTimeout(() => {
                    updateCharts(initialStats);
                }, 200);
            } else {
                // Si no hay datos, inicializar con datos vacíos para mostrar los gráficos
                updateCharts({
                    messagesByDay: [],
                    responseTimeByDay: [],
                    messageTypes: {}
                });
            }
            @else
            // Si no hay stats, inicializar con datos vacíos
            updateCharts({
                messagesByDay: [],
                responseTimeByDay: [],
                messageTypes: {}
            });
            @endif
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCharts);
        } else {
            // DOM ya está listo
            initializeCharts();
        }

        // Función para cambiar entre vista de contacto y global
        function toggleView() {
            const toggleBtn = document.getElementById('view-toggle');
            const modeText = document.getElementById('view-mode-text');
            const indicator = document.getElementById('view-indicator');

            if (currentViewMode === 'contact') {
                currentViewMode = 'global';
                modeText.textContent = 'Todos los Chats';
                indicator.textContent = 'Mostrando estadísticas globales de todos los contactos';
                toggleBtn.style.background = '#37a9fe';
                updateStats(globalStats);
                updateCharts(globalStats);
                updateMessageTypesList(globalStats.messageTypes || {});
            } else {
                currentViewMode = 'contact';
                modeText.textContent = 'Contacto Actual';
                indicator.textContent = 'Mostrando estadísticas del contacto seleccionado';
                toggleBtn.style.background = '#005c4b';
                @if(isset($stats))
                updateStats(@json($stats));
                updateCharts(@json($stats));
                updateMessageTypesList(@json($stats['messageTypes'] ?? []));
                @endif
            }
        }

        // Función para actualizar las estadísticas
        function updateStats(stats) {
            const totalMessagesEl = document.getElementById('stat-total-messages');
            const messageGrowthEl = document.getElementById('stat-message-growth');
            const lastActivityEl = document.getElementById('stat-last-activity');
            const lastActivityDateEl = document.getElementById('stat-last-activity-date');
            const avgTimeEl = document.getElementById('stat-avg-time');
            const sentReceivedEl = document.getElementById('stat-sent-received');
            const ratioEl = document.getElementById('stat-ratio');
            const clientResponseRateEl = document.getElementById('stat-client-response-rate');
            const peakHourEl = document.getElementById('stat-peak-hour');
            const activeDayEl = document.getElementById('stat-active-day');
            const avgLengthEl = document.getElementById('stat-avg-length');
            const conversationsEl = document.getElementById('stat-conversations');
            const timeBetweenEl = document.getElementById('stat-time-between');
            const frequencyEl = document.getElementById('stat-frequency');

            if (totalMessagesEl) totalMessagesEl.textContent = stats.totalMessages || 0;
            if (messageGrowthEl) {
                const growth = parseFloat(stats.messageGrowth) || 0;
                messageGrowthEl.textContent = `${growth >= 0 ? '+' : ''}${growth.toFixed(1)}% vs mes anterior`;
                messageGrowthEl.className = `stat-change ${growth >= 0 ? 'positive' : 'negative'}`;
            }
            if (lastActivityEl) lastActivityEl.textContent = stats.lastActivity || 'Nunca';
            if (lastActivityDateEl) lastActivityDateEl.textContent = stats.lastActivityDate || 'N/A';
            if (avgTimeEl) avgTimeEl.textContent = stats.avgResponseTime || '0m';
            if (sentReceivedEl) sentReceivedEl.textContent = `${stats.sentMessages || 0} / ${stats.receivedMessages || 0}`;
            if (ratioEl) ratioEl.textContent = `Ratio: ${stats.sentReceivedRatio || 0}`;
            if (clientResponseRateEl) clientResponseRateEl.textContent = stats.clientResponseRate || '0%';
            if (peakHourEl) peakHourEl.textContent = stats.peakHour || 'N/A';
            if (activeDayEl) activeDayEl.textContent = `Día: ${stats.mostActiveDay || 'N/A'}`;
            if (avgLengthEl) avgLengthEl.textContent = stats.avgMessageLength || '0 caracteres';
            if (conversationsEl) conversationsEl.textContent = stats.conversations || 0;
            if (timeBetweenEl) timeBetweenEl.textContent = stats.avgTimeBetweenMessages || '0m';
            if (frequencyEl) frequencyEl.textContent = stats.frequencyPerDay || 0;

            // Actualizar indicadores adicionales
            const sentOnlyEl = document.getElementById('stat-sent-only');
            const receivedOnlyEl = document.getElementById('stat-received-only');
            const ratioOnlyEl = document.getElementById('stat-ratio-only');
            const buttonsEl = document.getElementById('stat-buttons');
            const interactionEl = document.getElementById('stat-interaction');
            const growthValueEl = document.getElementById('stat-growth-value');

            if (sentOnlyEl) sentOnlyEl.textContent = stats.sentMessages || 0;
            if (receivedOnlyEl) receivedOnlyEl.textContent = stats.receivedMessages || 0;
            if (ratioOnlyEl) {
                ratioOnlyEl.textContent = stats.sentReceivedRatio || 0;
                const ratioChange = ratioOnlyEl.parentElement.querySelector('.stat-change');
                if (ratioChange) {
                    ratioChange.textContent = (stats.sentReceivedRatio || 0) > 1 ? 'Más enviados' : 'Más recibidos';
                    ratioChange.className = `stat-change ${(stats.sentReceivedRatio || 0) > 1 ? 'positive' : 'negative'}`;
                }
            }
            if (buttonsEl) buttonsEl.textContent = stats.buttonMessagesRate || '0%';
            if (interactionEl) interactionEl.textContent = stats.interactionRate || '0%';
            if (growthValueEl) {
                const growth = parseFloat(stats.messageGrowth) || 0;
                growthValueEl.textContent = `${growth >= 0 ? '+' : ''}${growth.toFixed(1)}%`;
                const growthChange = growthValueEl.parentElement.querySelector('.stat-change');
                if (growthChange) {
                    growthChange.className = `stat-change ${growth >= 0 ? 'positive' : 'negative'}`;
                }
            }
        }

        // Función para actualizar la lista de tipos de mensajes
        function updateMessageTypesList(messageTypes) {
            const topicsList = document.getElementById('topics-list');
            if (!topicsList) return;

            topicsList.innerHTML = '';
            const types = Object.keys(messageTypes);

            if (types.length === 0) {
                topicsList.innerHTML = '<div class="topic-item"><span class="topic-name">Sin datos disponibles</span><span class="topic-count">0</span></div>';
                return;
            }

            // Ordenar por cantidad
            const sortedTypes = types.sort((a, b) => messageTypes[b] - messageTypes[a]);

            sortedTypes.forEach(type => {
                const item = document.createElement('div');
                item.className = 'topic-item';
                item.innerHTML = `
                    <span class="topic-name">${type ? type.charAt(0).toUpperCase() + type.slice(1) : 'Texto'}</span>
                    <span class="topic-count">${messageTypes[type]}</span>
                `;
                topicsList.appendChild(item);
            });
        }

        // Función para manejar errores al cargar imágenes
        function handleImageError(imgElement, messageId) {
            // Verificar si ya se mostró el placeholder para evitar loops
            if (imgElement.parentElement && imgElement.parentElement.querySelector('.image-placeholder')) {
                return;
            }

            // Verificar el tipo de error haciendo una petición al servidor
            fetch(`/admin/messages/${messageId}/image`)
                .then(response => {
                    if (response.status === 410) {
                        // Imagen expirada
                        showImagePlaceholder(imgElement, 'La imagen ya no está disponible (puede haber expirado)');
                    } else {
                        // Otro tipo de error
                        showImagePlaceholder(imgElement, 'Imagen no disponible');
                    }
                })
                .catch(() => {
                    // Error de red o servidor
                    showImagePlaceholder(imgElement, 'Imagen no disponible');
                });
        }

        // Función para mostrar placeholder de imagen
        function showImagePlaceholder(imgElement, message) {
            if (!imgElement || !imgElement.parentElement) return;

            imgElement.style.display = 'none';
            const placeholder = document.createElement('div');
            placeholder.className = 'image-placeholder';
            placeholder.style.cssText = 'background: #202c33; border-radius: 8px; padding: 16px; margin-bottom: 8px; text-align: center;';
            placeholder.innerHTML = `
                <div style="width: 64px; height: 64px; border-radius: 12px; background: #37a9fe; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" fill="white"/>
                    </svg>
                </div>
                <span style="font-size: 14px; color: #e9edef; font-weight: 500;">${escapeHtml(message)}</span>
            `;
            imgElement.parentElement.insertBefore(placeholder, imgElement);
        }

        // Mobile Sidebar Toggle
        (function() {
            const sidebar = document.getElementById('waSidebar');
            const overlay = document.getElementById('waSidebarOverlay');
            const openBtn = document.getElementById('waOpenSidebar');
            const closeBtn = document.getElementById('waCloseSidebar');
            const avatarToggle = document.getElementById('waChatAvatarToggle');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('show');
                if (overlay) overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('show');
                if (overlay) overlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            // Abrir sidebar al hacer clic en el avatar (móvil)
            if (avatarToggle) {
                avatarToggle.addEventListener('click', function(e) {
                    // Solo abrir en móvil
                    if (window.innerWidth <= 991.98) {
                        e.preventDefault();
                        e.stopPropagation();
                        openSidebar();
                    }
                });
            }

            // Botón de menú hamburguesa (backup, oculto por defecto)
            if (openBtn) {
                openBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openSidebar();
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeSidebar();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeSidebar();
                });
            }

            // Cerrar sidebar cuando se selecciona un contacto en móvil
            const sidebarContacts = document.querySelectorAll('.wa-sidebar-contact');
            sidebarContacts.forEach(contact => {
                contact.addEventListener('click', function() {
                    // Solo cerrar en móvil
                    if (window.innerWidth <= 991.98) {
                        setTimeout(closeSidebar, 300);
                    }
                });
            });

            // Cerrar sidebar al redimensionar a desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991.98) {
                    closeSidebar();
                }
            });
        })();

        // Actualizar lista de contactos en tiempo real
        let contactsUpdateInterval = null;
        let currentContactId = {{ $contact->id }};

        function updateContactsList() {
            // Obtener el ID del contacto actual dinámicamente
            const currentContactIdInput = document.getElementById('current-contact-id');
            if (currentContactIdInput) {
                currentContactId = parseInt(currentContactIdInput.value) || currentContactId;
            }

            fetch(`/admin/chats/list/update?current_contact_id=${currentContactId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.contacts) {
                    const contactsContainer = document.querySelector('.wa-sidebar-contacts');
                    if (!contactsContainer) return;

                    // Obtener el ID del contacto actual dinámicamente (puede haber cambiado)
                    const currentContactIdInput = document.getElementById('current-contact-id');
                    const actualCurrentContactId = currentContactIdInput ? parseInt(currentContactIdInput.value) : currentContactId;

                    // Reconstruir la lista de contactos
                    contactsContainer.innerHTML = '';
                    data.contacts.forEach(contact => {
                        const isActive = contact.id == actualCurrentContactId;
                        const contactElement = document.createElement('a');
                        contactElement.href = 'javascript:void(0)';
                        contactElement.setAttribute('data-contact-id', contact.id);
                        contactElement.className = `wa-sidebar-contact${isActive ? ' active' : ''}`;

                        // Avatar
                        const avatar = document.createElement('div');
                        avatar.className = 'wa-sidebar-avatar';
                        avatar.textContent = (contact.name || 'C').charAt(0).toUpperCase();
                        contactElement.appendChild(avatar);

                        // Info
                        const info = document.createElement('div');
                        info.className = 'wa-sidebar-contact-info';

                        const name = document.createElement('div');
                        name.className = 'wa-sidebar-name';
                        name.textContent = contact.name || 'Cliente';
                        info.appendChild(name);

                        const phone = document.createElement('div');
                        phone.className = 'wa-sidebar-phone';
                        const phoneSpan = document.createElement('span');
                        phoneSpan.textContent = contact.phone_number;
                        phone.appendChild(phoneSpan);

                        if (contact.messages_count > 0) {
                            const badge = document.createElement('span');
                            badge.className = 'wa-message-count-badge';
                            badge.setAttribute('data-contact-id', contact.id);
                            badge.textContent = contact.messages_count;
                            phone.appendChild(badge);
                        }

                        // Verificar si hay mensajes nuevos comparando con el último mensaje visto
                        const lastSeen = localStorage.getItem(`last_seen_${contact.id}`);
                        let shouldShowIndicator = false;

                        if (contact.last_message_timestamp && contact.id != actualCurrentContactId) {
                            if (!lastSeen) {
                                // Si nunca se ha visto, mostrar indicador si es reciente (últimas 24 horas)
                                const messageDate = new Date(contact.last_message_timestamp);
                                const twentyFourHoursAgo = new Date(Date.now() - 24 * 60 * 60 * 1000);
                                shouldShowIndicator = messageDate > twentyFourHoursAgo;
                            } else {
                                // Comparar el último mensaje con el último visto
                                const lastSeenDate = new Date(lastSeen);
                                const lastMessageDate = new Date(contact.last_message_timestamp);
                                shouldShowIndicator = lastMessageDate > lastSeenDate;
                            }
                        }

                        if (shouldShowIndicator) {
                            const indicator = document.createElement('span');
                            indicator.className = 'wa-new-message-indicator';
                            indicator.setAttribute('data-contact-id', contact.id);
                            indicator.textContent = '●';
                            phone.appendChild(indicator);
                        }

                        info.appendChild(phone);

                        if (contact.last_client_message) {
                            const lastMsg = document.createElement('div');
                            lastMsg.className = 'wa-sidebar-last-message';
                            try {
                                const decoded = JSON.parse(contact.last_client_message);
                                if (decoded && typeof decoded === 'object') {
                                    if (decoded.type === 'button_reply' && decoded.button_reply) {
                                        lastMsg.textContent = decoded.button_reply.title;
                                    } else if (decoded.type === 'list_reply' && decoded.list_reply) {
                                        lastMsg.textContent = decoded.list_reply.title;
                                    } else if (decoded.title) {
                                        lastMsg.textContent = decoded.title;
                                    } else {
                                        lastMsg.textContent = contact.last_client_message.replace(/<[^>]*>/g, '');
                                    }
                                } else {
                                    lastMsg.textContent = contact.last_client_message.replace(/<[^>]*>/g, '');
                                }
                            } catch (e) {
                                lastMsg.textContent = contact.last_client_message.replace(/<[^>]*>/g, '');
                            }
                            info.appendChild(lastMsg);
                        }

                        contactElement.appendChild(info);
                        contactsContainer.appendChild(contactElement);

                        // Agregar evento click
                        contactElement.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const contactId = this.getAttribute('data-contact-id');
                            if (contactId) {
                                loadContactChat(contactId);
                            }
                        });
                    });

                    // Scroll al contacto activo si existe
                    if (activeContactId) {
                        const newActiveContact = document.querySelector(`.wa-sidebar-contact[data-contact-id="${activeContactId}"]`);
                        if (newActiveContact) {
                            newActiveContact.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error actualizando lista de contactos:', error);
            });
        }

        // Iniciar actualización periódica de contactos (cada 5 segundos)
        function startContactsUpdate() {
            if (contactsUpdateInterval) {
                clearInterval(contactsUpdateInterval);
            }
            contactsUpdateInterval = setInterval(updateContactsList, 5000);
            // Primera actualización inmediata
            updateContactsList();
        }

        // Iniciar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', startContactsUpdate);
        } else {
            startContactsUpdate();
        }
</script>
@endsection

