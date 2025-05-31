/* eslint-disable @typescript-eslint/no-explicit-any */
/* eslint-disable @typescript-eslint/no-unused-vars */
import React, { useState } from "react";
import axios from "axios";
axios.defaults.baseURL = "https://huge-badger-happily.ngrok-free.app/"
import { router } from "@inertiajs/react";

type Contact = {
    id: number;
    name: string;
    last_name: string;
    phone: string;
    email: string;
    course: string;
};

type ContactFormProps = {
    contact?: Contact;
    onSubmit: (data: Omit<Contact, "id">) => void;
    onCancel: () => void;
    loading: boolean;
};

const buttonStyle: React.CSSProperties = {
    padding: "8px 16px",
    margin: "0 4px",
    border: "none",
    borderRadius: "4px",
    background: "#1976d2",
    color: "#fff",
    cursor: "pointer",
    transition: "background 0.2s",
};

const buttonHoverStyle: React.CSSProperties = {
    background: "#1565c0",
};

const dangerButtonStyle: React.CSSProperties = {
    ...buttonStyle,
    background: "#d32f2f",
};

const dangerButtonHoverStyle: React.CSSProperties = {
    background: "#b71c1c",
};

const inputStyle: React.CSSProperties = {
    padding: "8px",
    margin: "4px 0",
    borderRadius: "4px",
    border: "1px solid #ccc",
    width: "100%",
    boxSizing: "border-box",
};

const formStyle: React.CSSProperties = {
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    gap: "8px",
    background: "#f5f5f5",
    padding: "24px",
    borderRadius: "8px",
    boxShadow: "0 2px 8px rgba(0,0,0,0.07)",
    minWidth: "320px",
    color: "#333",
};

const spinnerStyle: React.CSSProperties = {
    border: "4px solid #e3f2fd",
    borderTop: "4px solid #1976d2",
    borderRadius: "50%",
    width: 28,
    height: 28,
    animation: "spin 1s linear infinite",
    margin: "0 auto"
};

// Keyframes for spinner
const spinnerKeyframes = `
@keyframes spin {
  0% { transform: rotate(0deg);}
  100% { transform: rotate(360deg);}
}
`;

const Spinner: React.FC = () => (
    <>
        <style>{spinnerKeyframes}</style>
        <div style={spinnerStyle} />
    </>
);

const ContactForm: React.FC<ContactFormProps> = ({ contact, onSubmit, onCancel, loading }) => {
    const [form, setForm] = useState<Omit<Contact, "id">>({
        name: contact?.name || "",
        last_name: contact?.last_name || "",
        phone: contact?.phone || "",
        email: contact?.email || "",
        course: contact?.course || ""
    });

    const [hovered, setHovered] = useState<string | null>(null);

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(form);
    };

    return (
        <form onSubmit={handleSubmit} style={formStyle}>
            <input name="name" placeholder="Nombre" value={form.name} onChange={handleChange} required style={inputStyle} disabled={loading} />
            <input name="last_name" placeholder="Apellido" value={form.last_name} onChange={handleChange} required style={inputStyle} disabled={loading} />
            <input name="phone" placeholder="Teléfono" value={form.phone} onChange={handleChange} required style={inputStyle} disabled={loading} />
            <input name="email" placeholder="Email" value={form.email} onChange={handleChange} required style={inputStyle} disabled={loading} />
            <input name="course" placeholder="Curso" value={form.course} onChange={handleChange} required style={inputStyle} disabled={loading} />
            <div style={{ display: "flex", gap: "8px", marginTop: 8 }}>
                <button
                    type="submit"
                    style={hovered === "guardar" ? { ...buttonStyle, ...buttonHoverStyle } : buttonStyle}
                    onMouseEnter={() => setHovered("guardar")}
                    onMouseLeave={() => setHovered(null)}
                    disabled={loading}
                >
                    {loading ? <Spinner /> : "Guardar"}
                </button>
                <button
                    type="button"
                    style={hovered === "cancelar" ? { ...dangerButtonStyle, ...dangerButtonHoverStyle } : dangerButtonStyle}
                    onClick={onCancel}
                    onMouseEnter={() => setHovered("cancelar")}
                    onMouseLeave={() => setHovered(null)}
                    disabled={loading}
                >
                    Cancelar
                </button>
            </div>
        </form>
    );
};

const tableStyle: React.CSSProperties = {
    marginTop: 16,
    borderCollapse: "collapse",
    width: "100%",
    background: "#fff",
    boxShadow: "0 2px 8px rgba(0,0,0,0.07)",
    color: "#333",
};

const thTdStyle: React.CSSProperties = {
    padding: "12px 8px",
    textAlign: "center",
    border: "1px solid #e0e0e0",
};

const headerStyle: React.CSSProperties = {
    textAlign: "center",
    marginBottom: 24,
    color: "#1976d2",
};

const containerStyle: React.CSSProperties = {
    minHeight: "100vh",
    display: "flex",
    flexDirection: "column",
    alignItems: "center",
    justifyContent: "flex-start",
    background: "#e3f2fd",
    padding: "40px 0",
};

type ContactsIndexProps = {
    contacts: Contact[];
};

const ContactsIndex: React.FC<ContactsIndexProps> = ({ contacts }) => {
    const [editing, setEditing] = useState<Contact | null>(null);
    const [showForm, setShowForm] = useState(false);
    const [hoveredRow, setHoveredRow] = useState<number | null>(null);
    const [hoveredBtn, setHoveredBtn] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);
    const [deletingId, setDeletingId] = useState<number | null>(null);


    const handleCreate = async (data: Omit<Contact, "id">) => {
        setLoading(true);
        try {
            const response = await axios.post("/api/contacts", data);
            alert(response.data.message || "Contacto creado exitosamente");
            setShowForm(false);
            router.reload();
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        } catch (error: any) {
            console.error(error);
            if (error.response && error.response.data?.message) {
                if(error.response.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat().join(", ");
                    alert("Errores: " + errors);
                }
                else {
                    alert("Error: " + error.response.data.message);
                }
            }
            else {
                alert("Ocurrió un error al crear el contacto.");
            }
        } finally {
            setLoading(false);
            router.reload();
        }
    };

    const handleUpdate = async (data: Omit<Contact, "id">) => {
        if (!editing) return;
        setLoading(true);
        try {
            const response = await axios.put(`/api/contacts/${editing.id}`, data);
            alert(response.data.message || "Contacto actualizado exitosamente");
            setEditing(null);
            setShowForm(false);
            router.reload();
        } catch (error: any) {
            console.error(error);
            if (error.response && error.response.data?.message) {
                if(error.response.data?.errors) {
                    const errors = Object.values(error.response.data.errors).flat().join(", ");
                    alert("Errores: " + errors);
                }
                else {
                    alert("Error: " + error.response.data.message);
                }
            } else {
                alert("Ocurrió un error al actualizar el contacto.");
            }
        } finally {
            setLoading(false);
        }
    };



    return (
        <div style={containerStyle}>
            <h1 style={headerStyle}>Contactos</h1>
            {showForm ? (
                <ContactForm
                    contact={editing || undefined}
                    onSubmit={editing ? handleUpdate : handleCreate}
                    onCancel={() => {
                        setShowForm(false);
                        setEditing(null);
                    }}
                    loading={loading}
                />
            ) : (
                <button
                    style={hoveredBtn === "nuevo" ? { ...buttonStyle, ...buttonHoverStyle } : buttonStyle}
                    onClick={() => setShowForm(true)}
                    onMouseEnter={() => setHoveredBtn("nuevo")}
                    onMouseLeave={() => setHoveredBtn(null)}
                    disabled={loading}
                >
                    {loading ? <Spinner /> : "Nuevo Contacto"}
                </button>
            )}
            <table style={tableStyle}>
                <thead>
                    <tr>
                        <th style={thTdStyle}>Nombre</th>
                        <th style={thTdStyle}>Apellido</th>
                        <th style={thTdStyle}>Teléfono</th>
                        <th style={thTdStyle}>Email</th>
                        <th style={thTdStyle}>Curso</th>
                        <th style={thTdStyle}>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {contacts.map((c) => (
                        <tr
                            key={c.id}
                            style={{
                                background: hoveredRow === c.id ? "#e3f2fd" : undefined,
                                transition: "background 0.2s",
                            }}
                            onMouseEnter={() => setHoveredRow(c.id)}
                            onMouseLeave={() => setHoveredRow(null)}
                        >
                            <td style={thTdStyle}>{c.name}</td>
                            <td style={thTdStyle}>{c.last_name}</td>
                            <td style={thTdStyle}>{c.phone}</td>
                            <td style={thTdStyle}>{c.email}</td>
                            <td style={thTdStyle}>{c.course}</td>
                            <td style={thTdStyle}>
                                <button
                                    style={hoveredBtn === `edit-${c.id}` ? { ...buttonStyle, ...buttonHoverStyle } : buttonStyle}
                                    onClick={() => { setEditing(c); setShowForm(true); }}
                                    onMouseEnter={() => setHoveredBtn(`edit-${c.id}`)}
                                    onMouseLeave={() => setHoveredBtn(null)}
                                    disabled={loading}
                                >
                                    Editar
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default ContactsIndex;
